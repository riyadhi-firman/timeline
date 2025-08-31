<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobReportResource\Pages;
use App\Models\JobReport;
use App\Models\Schedule;
use App\Models\Technician;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\JobReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class JobReportResource extends Resource
{
    protected static ?string $model = JobReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Jadwal Pekerjaan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Pilih jadwal dan teknisi yang bertanggung jawab')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Select::make('schedule_id')
                    ->label('Jadwal')
                    ->options(function () {
                        $user = auth()->user();
                        $query = Schedule::query();

                        // Jika user adalah teknisi, hanya tampilkan jadwal yang ditugaskan padanya
                        if ($user->technician) {
                            $query->whereHas('technicians', function ($q) use ($user) {
                                $q->where('technician_id', $user->technician->id);
                            });
                        }

                        // Jika user adalah supervisor, hanya tampilkan jadwal teknisi di divisinya
                        if ($user->supervisor) {
                            $query->whereHas('technicians', function ($q) use ($user) {
                                $q->where('division_id', $user->supervisor->division_id);
                            });
                        }

                        return $query->get()->pluck('title', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $schedule = Schedule::find($state);
                            if ($schedule) {
                                // Jika user adalah teknisi, set teknisi_id otomatis
                                $user = auth()->user();
                                if ($user->technician) {
                                    $set('technician_id', $user->technician->id);
                                }
                            }
                        }
                    }),

                    Forms\Components\Select::make('technician_id')
                    ->label('Teknisi')
                    ->options(function (callable $get) {
                        $scheduleId = $get('schedule_id');
                        if (! $scheduleId) {
                            return Technician::with('user')->get()->mapWithKeys(function ($technician) {
                                return [$technician->id => $technician->user?->name ?? 'Teknisi #' . $technician->id];
                            });
                        }

                        $schedule = Schedule::with('technicians.user')->find($scheduleId);
                        if (! $schedule) {
                            return Technician::with('user')->get()->mapWithKeys(function ($technician) {
                                return [$technician->id => $technician->user?->name ?? 'Teknisi #' . $technician->id];
                            });
                        }

                        // Gunakan mapWithKeys dengan pengecekan null
                        return $schedule->technicians->mapWithKeys(function ($technician) {
                            return [$technician->id => $technician->user?->name ?? 'Teknisi #' . $technician->id];
                        });
                    })
                    ->searchable()
                    ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Laporan')
                    ->description('Isi detail pekerjaan yang telah dilakukan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('report_content')
                    ->label('Isi Laporan')
                    ->required()
                    ->rows(5),
                    ]),

                Forms\Components\Section::make('Material')
                    ->description('Daftar material yang digunakan dalam pekerjaan')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Forms\Components\Repeater::make('materials_used_items')
                    ->label('Material yang Digunakan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Material')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->createItemButtonLabel('Tambah Material')
                    ->afterStateHydrated(function ($state, callable $set, $record) {
                        if ($record && $record->materials_used) {
                            // Convert UUID-keyed object to simple array for Repeater
                            $materials = collect($record->materials_used)->values()->toArray();
                            $set('materials_used_items', $materials);
                        }
                    })
                    ->dehydrated(false),

                Forms\Components\Hidden::make('materials_used')
                    ->afterStateHydrated(function ($state, callable $set, $record) {
                        if ($record && $record->materials_used) {
                            $set('materials_used', $record->materials_used);
                        }
                    })
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        return $get('materials_used_items');
                    }),
                    ]),

                Forms\Components\Section::make('Dokumentasi')
                    ->description('Upload foto sebelum/sesudah dan tanda tangan')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Forms\Components\FileUpload::make('before_image')
                    ->label('Foto Sebelum Pekerjaan')
                    ->image()
                    ->directory('job-reports/before')
                    ->maxSize(5120) // 5MB
                    ->helperText('Maksimal 5MB'),

                Forms\Components\FileUpload::make('after_image')
                    ->label('Foto Setelah Pekerjaan')
                    ->image()
                    ->directory('job-reports/after')
                    ->maxSize(5120) // 5MB
                    ->helperText('Maksimal 5MB'),

                Forms\Components\FileUpload::make('customer_signature')
                    ->label('Tanda Tangan Pelanggan')
                    ->image()
                    ->directory('job-reports/signatures')
                    ->helperText('Upload gambar tanda tangan pelanggan'),
                    ])->columns(3),

                Forms\Components\Section::make('Status Penyelesaian')
                    ->description('Informasi status dan catatan penyelesaian')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Select::make('completion_status')
                    ->label('Status Penyelesaian')
                    ->options([
                        'completed' => 'Selesai',
                        'partial' => 'Sebagian Selesai',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('completed'),

                Forms\Components\Textarea::make('completion_notes')
                    ->label('Catatan Penyelesaian')
                    ->rows(3),

                Forms\Components\DateTimePicker::make('reported_at')
                    ->label('Waktu Laporan')
                    ->required()
                    ->default(now()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated(['10', '25', '50'])
            ->defaultSort('reported_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('schedule.title')
                    ->label('Jadwal')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('technician.user.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('report_content')
                    ->label('Isi Laporan')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->wrap()
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('materials_used')
                    ->label('Material')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        
                        // Handle case where state might be a string or invalid format
                        if (!is_array($state)) {
                            return '-';
                        }
                        
                        // Handle both array format and object with UUID keys format
                        $materials = collect($state)->map(function ($item, $key) {
                            // If the item is an array with UUID keys, extract the actual item data
                            if (is_array($item) && isset($item['name'])) {
                                // This is the expected format
                                if (!isset($item['quantity']) || !isset($item['unit'])) {
                                    return null;
                                }
                                return "{$item['name']} ({$item['quantity']} {$item['unit']})";
                            }
                            
                            // If the key looks like a UUID and item is an array, it might be the UUID key format
                            if (is_string($key) && strlen($key) > 20 && is_array($item)) {
                                if (!isset($item['name']) || !isset($item['quantity']) || !isset($item['unit'])) {
                                    return null;
                                }
                                return "{$item['name']} ({$item['quantity']} {$item['unit']})";
                            }
                            
                            return null;
                        })->filter()->join(', ');
                        
                        return $materials ?: '-';
                    })
                    ->wrap()
                    ->icon('heroicon-o-wrench-screwdriver'),

                Tables\Columns\TextColumn::make('completion_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'partial' => 'warning',
                        'cancelled' => 'danger',
                        'pending' => 'info',
                        default => 'secondary',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'completed' => 'heroicon-o-check-circle',
                        'partial' => 'heroicon-o-clock',
                        'cancelled' => 'heroicon-o-x-circle',
                        'pending' => 'heroicon-o-clock',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'partial' => 'Sebagian Selesai',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('reported_at')
                    ->label('Waktu Laporan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                Tables\Columns\ImageColumn::make('before_image')
                    ->label('Foto Sebelum')
                    ->circular()
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->openUrlInNewTab(),

                Tables\Columns\ImageColumn::make('after_image')
                    ->label('Foto Sesudah')
                    ->circular()
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('completion_status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'partial' => 'Sebagian Selesai',
                        'pending' => 'Tertunda',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\Filter::make('reported_at')
                    ->form([
                        Forms\Components\DatePicker::make('reported_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('reported_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['reported_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reported_at', '>=', $date),
                            )
                            ->when(
                                $data['reported_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reported_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return Excel::download(new JobReportsExport, 'job-reports.xlsx');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Jika user bukan super admin, filter berdasarkan divisi
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->whereHas('schedule.technicians.division', function ($query) use ($userDivisionId) {
                    $query->where('id', $userDivisionId);
                });
            }
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobReports::route('/'),
            'create' => Pages\CreateJobReport::route('/create'),
            'edit' => Pages\EditJobReport::route('/{record}/edit'),
        ];
    }
}

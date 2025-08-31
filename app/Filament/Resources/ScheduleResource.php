<?php

namespace App\Filament\Resources;

use App\Exports\SchedulesExport;
use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use App\Models\Technician;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Jadwal Pekerjaan';

    // Tambahkan metode ini untuk menampilkan badge count
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Opsional: Ubah warna badge (default: primary)
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary'; // Opsi lain: 'success', 'warning', 'danger', 'secondary'
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Teknisi')
                    ->description('Pilih teknisi yang akan ditugaskan')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Forms\Components\Select::make('technicians')
                            ->multiple()
                            ->relationship(
                                name: 'technicians',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = auth()->user();
                                    $supervisor = $user->supervisor;
                                    if ($supervisor) {
                                        return $query->where('division_id', $supervisor->division_id);
                                    }
                                    return $query;
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn (Technician $record) => $record->user->name)
                            ->label('Teknisi')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('division_id')
                                    ->relationship('division', 'name')
                                    ->required(),
                            ])
                            ->optionsLimit(15)
                            ->helperText('Pilih teknisi yang akan mengerjakan pekerjaan ini')
                            ->placeholder('Pilih teknisi')
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Pelanggan')
                    ->description('Masukkan informasi pelanggan')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Pelanggan')
                            ->placeholder('Masukkan nama pelanggan'),
                        Forms\Components\TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Nomor Telepon Pelanggan')
                            ->placeholder('Contoh: 08123456789')
                            ->helperText('Format: 08xx-xxxx-xxxx'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Pekerjaan')
                    ->description('Masukkan detail pekerjaan yang akan dilakukan')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Judul Pekerjaan')
                            ->placeholder('Masukkan judul pekerjaan'),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255)
                            ->label('Lokasi')
                            ->placeholder('Masukkan lokasi pekerjaan'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_time')
                                    ->required()
                                    ->label('Waktu Mulai')
                                    ->placeholder('Pilih waktu mulai')
                                    ->seconds(false)
                                    ->timezone('Asia/Jakarta')
                                    ->helperText('Format: DD/MM/YYYY HH:mm'),
                                Forms\Components\DateTimePicker::make('end_time')
                                    ->required()
                                    ->label('Waktu Selesai')
                                    ->placeholder('Pilih waktu selesai')
                                    ->seconds(false)
                                    ->timezone('Asia/Jakarta')
                                    ->after('start_time')
                                    ->helperText('Format: DD/MM/YYYY HH:mm'),
                            ]),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Dijadwalkan',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('scheduled')
                            ->label('Status')
                            ->placeholder('Pilih status')
                            ->helperText('Status awal pekerjaan'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Masukkan informasi tambahan yang diperlukan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Textarea::make('equipment_needed')
                            ->label('Perangkat/Tool yang Diperlukan')
                            ->helperText('Daftar perangkat atau tool yang perlu dibawa untuk pekerjaan ini')
                            ->placeholder('Contoh: - Obeng\n- Multimeter\n- Kabel LAN')
                            ->rows(3),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->label('Catatan')
                            ->placeholder('Masukkan catatan tambahan jika ada')
                            ->helperText('Catatan tambahan untuk pekerjaan ini')
                            ->rows(3),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }

    // Tambahkan action ini ke method table()
    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated(['10', '25', '50'])
            ->defaultSort('start_time', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                $supervisor = $user->supervisor;
                if ($supervisor) {
                    return $query->whereHas('technicians', function ($query) use ($supervisor) {
                        $query->where('division_id', $supervisor->division_id);
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('technicians.user.name')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->sortable()
                    ->label('Teknisi')
                    ->icon('heroicon-o-user-group'),
                Tables\Columns\TextColumn::make('technicians.division.name')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->sortable()
                    ->label('Divisi')
                    ->icon('heroicon-o-building-office-2'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Judul Pekerjaan')
                    ->wrap()
                    ->icon('heroicon-o-briefcase'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->label('Lokasi')
                    ->icon('heroicon-o-map-pin'),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Waktu Mulai')
                    ->icon('heroicon-o-clock')
                    ->color('success'),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Waktu Selesai')
                    ->icon('heroicon-o-clock')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'scheduled' => 'heroicon-o-calendar',
                        'in_progress' => 'heroicon-o-play',
                        'completed' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->label('Nama Pelanggan')
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable()
                    ->label('Telepon Pelanggan')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Nomor telepon disalin')
                    ->copyMessageDuration(1500),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division')
                    ->relationship('technicians.division', 'name')
                    ->label('Divisi'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return Excel::download(new SchedulesExport, 'schedules.xlsx');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Action lain yang sudah ada
                Tables\Actions\Action::make('createReport')
                    ->label('Buat Laporan')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Schedule $record): string => route('filament.admin.resources.job-reports.create', ['schedule_id' => $record->id]))
                    ->visible(fn (Schedule $record): bool => $record->status === 'in_progress' || $record->status === 'completed'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}

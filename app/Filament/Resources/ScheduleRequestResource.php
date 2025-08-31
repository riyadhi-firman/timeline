<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleRequestResource\Pages;
use App\Filament\Resources\ScheduleRequestResource\RelationManagers;
use App\Models\ScheduleRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use App\Exports\ScheduleRequestsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleRequestImporter;
use Filament\Tables\Actions\ImportAction;

class ScheduleRequestResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Schedule Requests';

    protected static ?string $navigationGroup = 'Jadwal Pekerjaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelanggan')
                    ->description('Masukkan informasi pelanggan yang meminta jadwal')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama')
                            ->required()
                            ->placeholder('Masukkan nama pelanggan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Nomor Telepon')
                            ->required()
                            ->tel()
                            ->placeholder('Contoh: 08123456789')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Permintaan')
                    ->description('Masukkan detail permintaan jadwal')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->placeholder('Masukkan judul permintaan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->placeholder('Masukkan lokasi pekerjaan')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->placeholder('Jelaskan detail pekerjaan yang diminta')
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('equipment_needed')
                            ->label('Peralatan yang Dibutuhkan')
                            ->placeholder('Sebutkan peralatan yang dibutuhkan (opsional)')
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->placeholder('Tambahkan catatan lain jika ada (opsional)')
                            ->maxLength(65535),
                    ]),

                Forms\Components\Hidden::make('requested_by')
                    ->default(auth()->id())
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Nomor telepon disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    }),
                Tables\Columns\TextColumn::make('requester.name')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-user-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return Excel::download(new ScheduleRequestsExport, 'schedule-requests.xlsx');
                    }),
                Tables\Actions\ImportAction::make('import')
                    ->label('Import')
                    ->icon('heroicon-o-document-arrow-up')
                    ->importer(ScheduleRequestImporter::class)
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->action(function (ScheduleRequest $record) {
                        $record->update([
                            'approved_by' => auth()->id(),
                            'approved_at' => now()
                        ]);
                        $schedule = $record->toSchedule();
                        
                        // Redirect ke halaman edit schedule
                        return redirect()->to(ScheduleResource::getUrl('edit', ['record' => $schedule]));
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ScheduleRequest $record): bool => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->action(function (ScheduleRequest $record) {
                        $record->update(['status' => 'rejected']);
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (ScheduleRequest $record): bool => $record->status === 'pending'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
                // Filter berdasarkan divisi dari user yang membuat request
                $query->whereHas('requester.supervisor', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduleRequests::route('/'),
            'create' => Pages\CreateScheduleRequest::route('/create'),
            'edit' => Pages\EditScheduleRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
}

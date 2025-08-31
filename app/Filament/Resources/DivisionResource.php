<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisionResource\Pages;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Manajemen Divisi';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $query = static::getModel()::query();
        
        // Jika user bukan super admin, filter berdasarkan divisi
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->where('id', $userDivisionId);
            }
        }
        
        return $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Divisi')
                    ->description('Masukkan informasi detail divisi')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Divisi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama divisi')
                            ->helperText('Nama lengkap divisi')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Divisi')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: DIV-001')
                            ->helperText('Kode unik untuk identifikasi divisi')
                            ->prefix('DIV-'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(65535)
                            ->placeholder('Deskripsi tugas dan tanggung jawab divisi')
                            ->helperText('Jelaskan peran dan tanggung jawab divisi ini')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Divisi')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Division $record): string => $record->code)
                    ->icon('heroicon-o-building-office')
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nama divisi disalin!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Divisi')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => "DIV-{$state}")
                    ->copyable()
                    ->copyMessage('Kode divisi disalin!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->searchable()
                    ->icon('heroicon-o-document-text')
                    ->wrap()
                    ->tooltip(fn (Division $record): string => $record->description),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateHeading('Belum ada divisi')
            ->emptyStateDescription('Mulai dengan membuat divisi baru')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Divisi')
                    ->icon('heroicon-o-plus'),
            ])
            ->striped()
            ->defaultSort('name', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Jika user bukan super admin, filter berdasarkan divisi
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->where('id', $userDivisionId);
            }
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}

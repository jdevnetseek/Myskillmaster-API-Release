<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePlayStoreSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('play-store.store_url', null);
    }

    public function down(): void
    {
        $this->migrator->delete('play-store.store_url');
    }
}

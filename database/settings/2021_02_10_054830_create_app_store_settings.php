<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateAppStoreSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app-store.store_url', null);
    }

    public function down(): void
    {
        $this->migrator->delete('app-store.store_url');
    }
}

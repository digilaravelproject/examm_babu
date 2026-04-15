<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateAiSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ai.gemini_api_key', config('services.gemini.key') ?? '');
        $this->migrator->add('ai.model_name', 'gemini-1.5-flash');
    }
}

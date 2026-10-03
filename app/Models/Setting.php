<?php

require_once __DIR__ . '/Model.php';

class Setting extends Model {
    protected $table = 'settings';
    protected $fillable = ['key', 'value', 'group', 'description'];
    protected $dates = ['created_at', 'updated_at'];

    public static function get($key, $default = null) {
        $db = db();
        $setting = $db->table('settings')->where('key', $key)->first();
        return $setting ? $setting['value'] : $default;
    }

    public static function set($key, $value, $group = 'general', $description = '') {
        $db = db();
        $existing = $db->table('settings')->where('key', $key)->first();
        
        if ($existing) {
            return $db->table('settings')->where('key', $key)->update([
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]);
        } else {
            return $db->table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]);
        }
    }

    public static function getGroup($group) {
        $db = db();
        return $db->table('settings')->where('group', $group)->get();
    }

    public static function getAll() {
        $db = db();
        $settings = $db->table('settings')->get();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        return $result;
    }
}
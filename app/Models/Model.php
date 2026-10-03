<?php

#[AllowDynamicProperties]
abstract class Model implements ArrayAccess {
    protected $table;
    protected $fillable = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];

    public function __construct(array $attributes = [], $unguarded = false) {
        if ($unguarded) {
            foreach ($attributes as $key => $value) {
                $this->{$key} = $this->castValue($key, $value);
            }
        } else {
            $this->fill($attributes);
        }
    }

    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if ($key === 'id' || in_array($key, $this->fillable) || $this->isFillable($key)) {
                $this->{$key} = $this->castValue($key, $value);
            }
        }
        return $this;
    }

    public function offsetExists($offset): bool {
        return isset($this->{$offset});
    }

    public function offsetGet($offset): mixed {
        return $this->{$offset} ?? null;
    }

    public function offsetSet($offset, $value): void {
        $this->{$offset} = $this->castValue($offset, $value);
    }

    public function offsetUnset($offset): void {
        unset($this->{$offset});
    }

    protected function isFillable($key) {
        return empty($this->fillable) || in_array($key, $this->fillable);
    }

    protected function castValue($key, $value) {
        if (isset($this->casts[$key])) {
            switch ($this->casts[$key]) {
                case 'json':
                case 'array':
                    return is_string($value) ? json_decode($value, true) : $value;
                case 'boolean':
                    return (bool) $value;
                case 'integer':
                case 'int':
                    return (int) $value;
                case 'float':
                case 'double':
                    return (float) $value;
                case 'date':
                    return $value ? new DateTime($value) : null;
                case 'datetime':
                    return $value ? new DateTime($value) : null;
            }
        }
        return $value;
    }

    public function toArray() {
        $attributes = [];
        $internalKeys = ['table', 'fillable', 'hidden', 'casts', 'dates'];
        foreach ($this as $key => $value) {
            if (!in_array($key, $this->hidden) && !in_array($key, $internalKeys)) {
                $attributes[$key] = $value instanceof DateTime ? $value->format('Y-m-d H:i:s') : $value;
            }
        }
        return $attributes;
    }

    public function toJson() {
        return json_encode($this->toArray());
    }

    public static function find($id) {
        $instance = new static();
        $db = db();
        $result = $db->table($instance->table)->find($id);
        return $result ? new static($result, true) : null;
    }

    public static function all() {
        $instance = new static();
        $db = db();
        $results = $db->table($instance->table)->asModel(static::class)->get();
        return $results;
    }

    public static function where($column, $operator = null, $value = null) {
        $instance = new static();
        $db = db();
        $query = $db->table($instance->table)->asModel(static::class);
        
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        return $query->where($column, $operator, $value);
    }

    public static function whereIn($column, $values) {
        $instance = new static();
        $db = db();
        return $db->table($instance->table)->asModel(static::class)->whereIn($column, $values);
    }

    public static function first() {
        $instance = new static();
        $db = db();
        $result = $db->table($instance->table)->first();
        return $result ? new static($result, true) : null;
    }

    public static function count() {
        $instance = new static();
        $db = db();
        return $db->table($instance->table)->count();
    }

    public static function create(array $attributes) {
        $instance = new static();
        $db = db();
        $id = $db->table($instance->table)->insert($attributes);
        return $id ? static::find($id) : null;
    }

    public function save() {
        $db = db();
        $attributes = $this->toArray();
        
        if (isset($this->id)) {
            $db->table($this->table)->where('id', $this->id)->update($attributes);
            return true;
        } else {
            $this->id = $db->table($this->table)->insert($attributes);
            return $this->id !== false;
        }
    }

    public function delete() {
        if (!isset($this->id)) return false;
        $db = db();
        return $db->table($this->table)->where('id', $this->id)->delete() > 0;
    }

    public static function query() {
        $instance = new static();
        $db = db();
        return $db->table($instance->table)->asModel(static::class);
    }

    public function __get($key) {
        $vars = get_object_vars($this);
        if (array_key_exists($key, $vars)) {
            return $vars[$key];
        }
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }
        return null;
    }

    public function __set($key, $value) {
        $this->{$key} = $this->castValue($key, $value);
    }

    public function __isset($key) {
        return isset($this->{$key});
    }
}
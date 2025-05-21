<?php
namespace App\Core;

use App\Core\Model\Collection;
use App\Core\Model\CollectionInterface;
use PDO;

abstract class Model
{
    protected string $table;
    protected array $data = [];
    protected ?array $fields = null;
    protected string $primaryKey = 'id';
    protected array $foreignKeys = [];
    private ?CollectionInterface $collection = null;
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function describe(): array
    {
        if ($this->fields === null) {
            $stmt = $this->db->query("DESCRIBE {$this->table}");
            $this->fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $this->fields;
    }

    public function getId(): ?int
    {
        return (int)($this->data[$this->primaryKey] ?? null);
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getCollection(): Collection
    {
        if (!$this->collection instanceof CollectionInterface) {
            

        $collectionClass = class_exists(
            static::class . '\Collection',
            true
        )
            ? static::class . '\Collection'
            : Collection::class;

        $this->collection = new $collectionClass(static::class);

            if (!$this->collection instanceof CollectionInterface) {
                throw new \RuntimeException(
                    "Collection class $collectionClass must implement CollectionInterface"
                );
            }
        }

        return $this->collection;    
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql)->fetchAll();
    }

    public function listWhere(string $where,array $params,string $sort,string $dir,int $limit,int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} $where ORDER BY $sort $dir LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k=>$v){
            $stmt->bindValue(':'.$k,$v,is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR);
        }
        $stmt->bindValue(':lim',$limit,PDO::PARAM_INT);
        $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    

    public function load(int $id): static
    {
        $this->data = $this->find($id) ?? [];

        return $this;
    }

    public function isLoaded(): bool
    {
        return !empty($this->data) && isset($this->data[$this->getPrimaryKey()]);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->describe())) {
                $this->data[$key] = $value;
            }
        }
        return $this;
    }

    public function get(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    public function set(string $field, mixed $value): static
    {
        if (!in_array($field, $this->describe())) {
            throw new \Exception("Field $field does not exist in table {$this->table}");
        }
        $this->data[$field] = $value;
        return $this;
    }
    
    public function save(): static
    {
        unset($this->data['created_at']);
        unset($this->data['created_by']);
        unset($this->data['updated_by']);
        unset($this->data['updated_at']);

        if ($this->isLoaded()) {
            return $this->update($this->data);
        } else {
            return $this->create($this->data);
        }
    }

    public function create(array $data): static
    {
        unset($data[$this->primaryKey]);

        foreach ($data as $key => $value) {
            if (!in_array($key, $this->describe())) {
                unset($data[$key]);
            }
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }
        }

        $fields = implode(', ', array_keys($data));

        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            $this->data[$this->primaryKey] = $this->db->lastInsertId();
        } catch (\Throwable $e) {
            throw new \Exception("Error inserting data: " . $e->getMessage());
        }

        return $this;
    }

    public function update(array $data): static
    {
        $primaryKeyValue = $this->getId();

        if (empty($primaryKeyValue)) {
            throw new \Exception("No primary key value set for update");
        }

        // Remove primary key from data to avoid updating it
        unset($data[$this->primaryKey]);

        // Filter and encode data
        foreach ($data as $key => $value) {
            if ($key === $this->primaryKey) {
                continue;
            }

            if (!in_array($key, $this->describe())) {
                unset($data[$key]);
                continue;
            }
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }
        }

        if (empty($data)) {
            throw new \Exception("No data to update");
        }

        // Build SET part of SQL
        $fields = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $fields WHERE {$this->primaryKey} = :primary_key";

        // Add primary key to parameters
        $params = $data;
        $params['primary_key'] = $primaryKeyValue;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Reload data
        $this->load($primaryKeyValue);

        return $this;
    }

    public function delete(int $id): bool
    {
        $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id")
            ->execute(['id' => $id]);
        $this->data = [];
        
        return true;
    }
}

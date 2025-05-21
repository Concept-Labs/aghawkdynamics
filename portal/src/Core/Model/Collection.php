<?php

namespace App\Core\Model;

use App\Core\Database;
use App\Core\Model;
use PDO;
use Traversable;

class Collection implements CollectionInterface
{

    const ITEM_MODE_ARRAY = 0;
    const ITEM_MODE_OBJECT = 1;

    protected string $modelClass;
    protected ?string  $table = null;
    protected array $filters = [];
    protected array $sort = [];
    protected array $params = [];
    protected int $page = 1;
    protected ?string $rawSql = null;
    protected int $pageSize = 10;
    protected int $itemMode = self::ITEM_MODE_OBJECT;
    protected PDO $db;

    public function __construct(string|Model $model)
    {
        if (is_string($model)) {
            if (!class_exists($model)) {
                throw new \InvalidArgumentException("Model class $model does not exist");
            }
            if (!is_subclass_of($model, Model::class)) {
                throw new \InvalidArgumentException("Model class $model must extend App\\Core\\Model");
            }

            $this->modelClass = $model;
            $this->table = (new $model())->getTable();
        } else {
            if (!is_subclass_of($model, Model::class)) {
                throw new \InvalidArgumentException("Model class $model does not extend App\\Core\\Model");
            }
            $this->modelClass = get_class($model);
            $this->table = $model->getTable();
        }

        if (!$this->table) {
            throw new \RuntimeException("Model class $model does not have a table defined");
        }
        
        $this->db = Database::connect();
    }

    protected function getModelClass(): string
    {
        return $this->modelClass;
    }

    protected function createModel(array $data = []): Model
    {
        $model  = new $this->modelClass();
        if ($data) {
            $model->setData($data);
        }

        return $model;
    }

    protected function getTable(): string
    {
        return $this->table;
    }

    public function setItemMode(int $mode): static
    {
        if (!in_array($mode, [self::ITEM_MODE_ARRAY, self::ITEM_MODE_OBJECT])) {
            throw new \InvalidArgumentException("Item mode must be " . self::ITEM_MODE_ARRAY . " or " . self::ITEM_MODE_OBJECT);
        }

        $this->itemMode = $mode;

        return $this;
    }

    public function getItemMode(): int
    {
        return $this->itemMode;
    }

    public function addFilter(array $filter, string $operator = 'AND'): static
    {
        if (!in_array($operator, ['AND', 'OR'])) {
            throw new \InvalidArgumentException("Operator must be 'AND' or 'OR'");
        }

        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                $this->filters[] = [
                    'filter' => "$key IN (" . implode(',', array_fill(0, count($value), ":$key")) . ")",
                    'operator' => $operator
                ];
            } else {
                $this->filters[] = [
                    'filter' => "$key = :$key",
                    'operator' => $operator
                ];
            }

            $this->params[$key] = $value;
        }

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function sort(string $field, string $direction = 'ASC'): static
    {
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException("Direction must be 'ASC' or 'DESC'");
        }

        $this->sort[$field] = $direction;

        return $this;
    }

    public function getSort(): array
    {
        return $this->sort;
    }

    public function setPage(int $page): static
    {
        if ($page < 1) {
            throw new \InvalidArgumentException("Page must be greater than 0");
        }

        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setRawSql(string $sql): static
    {
        $this->rawSql = $sql;

        return $this;
    }
    public function getRawSql(): ?string
    {
        return $this->rawSql;
    }

    public function getSelect(): string
    {
        if ($this->rawSql) {
            return $this->rawSql;
        }

        $sql = "SELECT * FROM {$this->table}";

        if ($this->filters) {
            $sql .= ' WHERE 1=1 ' . implode(' ', array_map(function ($filter) {
                return $filter['operator'] . ' ' . $filter['filter'];
            }, $this->filters));
        }

        if ($this->sort) {
            $sql .= ' ORDER BY ' . implode(', ', array_map(function ($field, $direction) {
                return "$field $direction";
            }, array_keys($this->sort), $this->sort));
        }

        if ($this->pageSize > 0) {
            $offset = ($this->page - 1) * $this->pageSize;
            $sql .= " LIMIT :lim OFFSET :off";
            $this->params['lim'] = $this->pageSize;
            $this->params['off'] = $offset;
        }

        return $sql;
    }
    public function getParams(): array
    {
        return $this->params;
    }
    public function setPageSize(int $size): static
    {
        if ($size < 1) {
            throw new \InvalidArgumentException("Page size must be greater than 0");
        }

        $this->pageSize = $size;

        return $this;
    }
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function count(): int
    {
        $sql = $this->getSelect();
        $sql = "SELECT COUNT(*) FROM ($sql) AS count_query";
        $stmt = $this->db->prepare($sql);
        foreach ($this->params as $key => $value) {
            $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function fetch(): Traversable
    {
        $sql = $this->getSelect();
        $stmt = $this->db->prepare($sql);
        foreach ($this->params as $key => $value) {
            $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        if ($this->itemMode === self::ITEM_MODE_ARRAY) {
            return new \ArrayIterator($stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        return new \ArrayIterator(array_map([$this, 'createModel'], $stmt->fetchAll(PDO::FETCH_ASSOC)));
    }

    public function getIterator(): Traversable
    {
        return $this->fetch();
    }
    
    
}
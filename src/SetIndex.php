<?php

namespace ZzElastic;

class SetIndex
{
    protected $indexName = '';
    protected $indexType = '';

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @param string $indexName
     */
    public function setIndexName(string $indexName)
    {
        $this->indexName = $indexName;
    }

    /**
     * @return string
     */
    public function getIndexType(): string
    {
        return $this->indexType;
    }

    /**
     * @param string $indexType
     */
    public function setIndexType(string $indexType)
    {
        $this->indexType = $indexType;
    }

    public function toArray()
    {
        return [
            'index' => $this->getIndexName(),
            'type'  => $this->getIndexType(),
        ];
    }
}
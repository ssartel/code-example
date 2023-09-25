<?php

namespace System\Libs\Data;


use Exception;
use SplFileInfo;
use System\Exceptions\InvalidParamException;

class DataHandler
{
    private ParseDataInterface $parser;
    public function __construct(private SplFileInfo $fileInfo)
    {
        $this->getParser();
    }

    public function getParser(): void
    {
        $data = file_get_contents($this->fileInfo->getRealPath());

        switch ($this->fileInfo->getExtension()) {
            case 'json' :
                $this->parser = new JSON($data);
                break;
            default:
                throw new InvalidParamException('Needed parser is missing!');

        }
    }

    public function getFields(): array
    {
        $data = $this->parser->getData();

        $fields = array_keys($data[0]);
        unset($fields[array_key_last($fields)]);

        return $fields;
    }

    public function createIndex(string $field): void
    {
        $data = $this->parser->getData();

        usort($data, function($a, $b) use ($field) {
            if (is_numeric($a[$field]) && is_numeric($b[$field])) {
                return ($a[$field] < $b[$field]) ? -1 : 1;
            }

            return strcmp($a[$field], $b[$field]);
        });

        $tree = $this->buildTree($data, 0, count($data), $field);

        $encodedTree = $this->parser->encodeData($tree);

        if (file_put_contents(INDEXES . $field . '.' . $this->fileInfo->getExtension(), $encodedTree) === false) {
            throw new Exception('File with indexes was not created');
        }
    }

    protected function buildTree(array $data, int $start, int $end, string $field): array|null
    {
        static $counter = 1;

        if (($start > $end) || ($counter > count($data))) {
            return null;
        }

        $middle = (int)(($start + $end) / 2);

        if (!empty($data[$middle][$field])) {
            $node = [
                'index' => $data[$middle][$field],
                'value' => $data[$middle],
            ];
        }

        $counter++;

        $node['left'] = $this->buildTree($data, $start, $middle - 1, $field);
        $node['right'] = $this->buildTree($data, $middle + 1, $end, $field);

        return $node;
    }

    public function search(string $field, string $request): array
    {
        $tree = $this->getSearchArray($field);
        $indexedCount = 0;
        $indexedSearch = $this->indexedSearch($tree, $request, $indexedCount);

        $data = $this->parser->getData();
        $simpleCount = 0;
        $simpleSearch = $this->simpleSearch($data, $field, $request, $simpleCount);

        return [
            'indexedSearch' => [
                'result' => $indexedSearch,
                'count' => $indexedCount,
            ],
            'simpleSearch' => [
                'result' => $simpleSearch,
                'count' => $simpleCount,
            ],
        ];
    }

    private function getSearchArray(string $field): array|string
    {
        $indexPath = INDEXES . $field . '.' . $this->fileInfo->getExtension();
        $data = file_get_contents($indexPath);

        return $this->parser->decodeData($data);
    }

    private function indexedSearch(array|null $tree, string $request, int &$count): array
    {
        if ($tree === null) {
            return [];
        }

        $count++;
        if ($tree['index'] === $request) {
            return [$tree['value']];
        }

        if ($tree['index'] > $request) {
            return $this->indexedSearch($tree['left'], $request, $count);
        }

        return $this->indexedSearch($tree['right'], $request, $count);
    }

    private function simpleSearch(array $data, string $field, string $request, int &$count): array
    {
        $result = [];

        foreach ($data as $value) {
            $count++;

            if (isset($value[$field]) && $value[$field] === $request) {
                $result[] = $value;
                break;
            }
        }

        return $result;
    }
}

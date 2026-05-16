<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use System\Exception\SystemException;
use System\Upload\Upload;
use System\Validation\Validation;
use App\Core\Abstracts\{Repository, Resource};

abstract class Service {
    protected Upload $upload;
    protected Validation $validation;

    /**
     * Checks if a record exists
     */
    protected function check(Repository $repository, array $fields, Resource $resource, bool $create = true): void {
        $exist = $repository->findBy($fields);

        if ($create) {
            if (!empty($exist)) {
                throw new SystemException('Record already exists', 400);
            }
        } else {
            $id = $resource->property('id');
            if (!$repository->findOne($id)) {
                throw new SystemException('Record not found', 404);
            }
            if (!empty($exist) && $exist['id'] !== (int) $id) {
                throw new SystemException('Record already exists', 400);
            }
        }
    }

    /**
     * Validates data based on the given rules
     */
    public function validate(array $data, array $rules, ?array $labels = [], ?array $messages = []): void {
        $this->validation->data($data);
        $this->validation->rules($rules);
        $this->validation->labels($labels);
        $this->validation->messages($messages);

        if (!$this->validation->handle()) {
            throw new SystemException(json_encode($this->validation->errors()), 400);
        }
    }

    /**
     * Updates or creates translation records
     * For each language, the record is searched by `language_id` and `id` according to the given data
     * If the record exists, it is updated; otherwise, it is added as a new translation
     */
    final protected function translate(Repository $repository, array $fields, array $where, array $translations, string $table): void {
        foreach ($translations as $item) {
            if (!isset($item['language_id'])) {
                throw new SystemException('Language id is required', 400);
            }

            $langId  = $item['language_id'];
            $filter = array_intersect_key($item, array_flip($fields));
            unset($item['language_id']);
            $where = array_merge($where, [
                'language_id' => $langId
            ]);

            if ($repository->findBy($where, $table)) {
                $result = $repository->update($filter, $where, $table);
            } else {
                $result = $repository->create(array_merge($where, $filter), $table);
            }

            if ($result->affectedRows() <= 0) {
                throw new SystemException('Failed to record translate', 400);
            }
        }
    }

    /**
     * Uploads files and returns file paths
     */
    final protected function upload(?array $files, ?string $dir = null): array {
        if (empty($files) || !isset($files['name'])) {
            return [];
        }

        $this->upload->setDir($dir);
        return $this->upload->handle($files);
    }

    /**
     * Deletes a file
     */
    final protected function unlink(string|array|null $files = null): bool {
        if (empty($files) || $files === null) {
            return false;
        }

        return $this->upload->unlink($files);
    }
}

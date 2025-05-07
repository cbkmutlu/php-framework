<?php

declare(strict_types=1);

namespace App\Modules\Test\Services;

use App\Modules\Test\Repositories\TestRepository;
use System\Exception\SystemException;
use System\Jwt\Jwt;

class TestService {
   public function __construct(
      private TestRepository $repository,
      private Jwt $jwt
   ) {
   }

   public function getAllUser() {
      return $this->repository->getAll();
   }

   public function getUser(int $id) {
      // $result = $this->repository->getById($id);
      $result = $this->repository->user($id);
      if (empty($result)) {
         throw new SystemException('Record not found', 404);
      }

      return $result;
   }

   public function loginUser(array $data) {
      $result = $this->repository->login($data);
      if (empty($result)) {
         throw new SystemException('Invalid credentials', 401);
      }

      $payload = [
         'user_id' => $result['id'],
         'user_name' => $result['name'],
         'user_email' => $result['email'],
         'exp' => time() + 180000
      ];
      $token = $this->jwt->encode(payload: $payload);

      return $token;
   }

   public function updateUser(array $data) {
      // $result = $this->repository->getById($data['id']);
      // if (empty($result)) {
      //    throw new SystemException('Record not found', 404);
      // }

      $result = $this->repository->update($data);
      if (empty($result)) {
         throw new SystemException('Invalid data provided', 400);
      }

      return $result;
   }

   public function hardDeleteUser(int $id) {
      // $result = $this->repository->getById($id);
      // if (empty($result)) {
      //    throw new SystemException('Record not found', 404);
      // }

      $result = $this->repository->hardDelete($id);
      if (empty($result)) {
         throw new SystemException('Invalid data provided', 400);
      }

      return $result;
   }

   public function softDeleteUser(int $id) {
      // $result = $this->repository->getById($id);
      // if (empty($result)) {
      //    throw new SystemException('Record not found', 404);
      // }

      $result = $this->repository->softDelete($id);
      if (empty($result)) {
         throw new SystemException('Invalid data provided', 400);
      }

      return $result;
   }

   public function getBenchmark() {
      return $this->repository->benchmark();
   }

}

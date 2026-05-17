<?php

declare(strict_types=1);

namespace App\Modules\Approval;

use System\Http\{Request, Response};
use App\Core\Abstracts\Controller;
use App\Modules\Approval\{ApprovalRequest, ApprovalResponse, ApprovalService};

/**
 * @OA\Tag(name="Approval", description="Onay mekanizması işlemleri")
 */
class ApprovalController extends Controller {
    public function __construct(
        protected Response $response,
        protected Request $request,
        protected ApprovalService $service
    ) {
    }

    /**
     * @OA\Get(tags={"Approval"}, path="/approval/pending", summary="Bana atanmış bekleyen onayları listele",
     *    @OA\Response(response=200, description="Success")
     * )
     */
    public function getPending(): void {
        $user = $this->request->getUser();
        $userId = (int) $user['id'];
        $result = $this->service->getPending($userId);

        // Response map
        $list = array_map(function ($item) {
            $response = new ApprovalResponse();
            return $response->map($item);
        }, $result);

        $this->response->json($list);
    }

    /**
     * @OA\Get(tags={"Approval"}, path="/approval/my-flows", summary="Benim oluşturduğum onay süreçlerini listele",
     *    @OA\Response(response=200, description="Success")
     * )
     */
    public function getMyFlows(): void {
        $user = $this->request->getUser();
        $userId = (int) $user['id'];
        $result = $this->service->getMyFlows($userId);

        $list = array_map(function ($item) {
            $response = new ApprovalResponse();
            return $response->map($item);
        }, $result);

        $this->response->json($list);
    }

    /**
     * @OA\Get(tags={"Approval"}, path="/approval/{id}", summary="Onay süreci detayı",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
     * )
     */
    public function getById(int $id): void {
        $result = $this->service->getFlow($id);
        $response = new ApprovalResponse();
        $response->map($result);

        $this->response->json($response);
    }

    /**
     * @OA\Post(tags={"Approval"}, path="/approval/{id}/accept", summary="Adımı onayla",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *    @OA\RequestBody(required=false, @OA\JsonContent(
     *       @OA\Property(property="comment", type="string", example="Uygun bulunmuştur")
     *    ))
     * )
     */
    public function accept(int $id): void {
        $user = $this->request->getUser();
        $userId = (int) $user['id'];
        $json = $this->request->json();

        $request = new ApprovalRequest();
        $request->fill($json);

        $result = $this->service->accept($id, $userId, $request);
        $response = new ApprovalResponse();
        $response->map($result);

        $this->response->json($response);
    }

    /**
     * @OA\Post(tags={"Approval"}, path="/approval/{id}/reject", summary="Adımı reddet",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *    @OA\RequestBody(required=true, @OA\JsonContent(
     *       required={"reason"},
     *       @OA\Property(property="reason", type="string", example="Bütçe yetersiz"),
     *       @OA\Property(property="comment", type="string", example="Lütfen daha düşük fiyatlı bir alternatif bulun")
     *    ))
     * )
     */
    public function reject(int $id): void {
        $user = $this->request->getUser();
        $userId = (int) $user['id'];
        $json = $this->request->json();

        $request = new ApprovalRequest();
        $request->fill($json);

        $result = $this->service->reject($id, $userId, $request);
        $response = new ApprovalResponse();
        $response->map($result);

        $this->response->json($response);
    }

    /**
     * @OA\Post(tags={"Approval"}, path="/approval/{id}/cancel", summary="Süreci iptal et (Sadece talep sahibi)",
     *    @OA\Response(response=200, description="Success"),
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"))
     * )
     */
    public function cancel(int $id): void {
        $user = $this->request->getUser();
        $userId = (int) $user['id'];
        $result = $this->service->cancel($id, $userId);

        $response = new ApprovalResponse();
        $response->map($result);

        $this->response->json($response);
    }
}

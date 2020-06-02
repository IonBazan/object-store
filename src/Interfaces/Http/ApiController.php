<?php

declare(strict_types=1);

namespace App\Interfaces\Http;

use App\Domain\Model\ObjectEntry;
use App\Domain\Service\ObjectStoreInterface;
use DateTime;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/object")
 * @OA\Tag(name="Object Store")
 */
class ApiController extends AbstractController
{
    protected ObjectStoreInterface $objectStorage;

    public function __construct(ObjectStoreInterface $objectStorage)
    {
        $this->objectStorage = $objectStorage;
    }

    /**
     * @OA\RequestBody(
     *     description="Key-value pairs to store",
     *     required=true,
     *     @OA\JsonContent(example={"key": "value"}, @OA\Schema(type="object", additionalProperties=true))
     * )
     * @OA\Response(
     *     response="201",
     *     description="On success",
     *     headers={@OA\Header(header="X-Timestamp", @OA\Schema(type="integer"), description="Current server timestamp")}
     * )
     * @OA\Response(response="400", description="When provided object is not valid")
     *
     * @Route("", name="api_upsert_object", methods={"POST"}, defaults={"_format": "json"})
     */
    public function storeValue(Request $request)
    {
        $objects = json_decode($request->getContent(), true);

        if (!is_iterable($objects)) {
            throw new BadRequestHttpException();
        }

        foreach ($objects as $key => $object) {
            $entry = new ObjectEntry($key, $object);
            $this->objectStorage->store($entry, new DateTime());
        }

        return new JsonResponse(
            null,
            Response::HTTP_CREATED,
            ['X-Timestamp' => time()]
        );
    }

    /**
     * @OA\Parameter(name="key", in="path", description="Key to display value for", @OA\Schema(type="string"))
     * @OA\Parameter(name="timestamp", in="query", description="Timestamp of the value snapshot", @OA\Schema(type="integer"), required=false)
     * @OA\Response(
     *     response="200",
     *     description="Value for the key at given time (or now)",
     *     @OA\Schema(type="object", example="test-value"),
     *     headers={@OA\Header(header="X-Timestamp", schema=@OA\Schema(type="integer"), description="Current server timestamp")}
     * )
     * @OA\Response(response="400", description="When provided timestamp is invalid")
     * @OA\Response(response="404", description="When key is not found (for provided time)")
     *
     * @Route("/{key}", name="api_get_value", methods={"GET"}, defaults={"_format": "json"})
     */
    public function getValue(string $key, Request $request)
    {
        $date = DateTime::createFromFormat('U', (string) $request->get('timestamp', time()));

        if (!$date instanceof DateTime) {
            throw new BadRequestHttpException('Invalid time');
        }

        $value = $this->objectStorage->get($key, $date);

        if (!$value) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse(
            $value->getValue(),
            Response::HTTP_OK,
            ['X-Timestamp' => time()]
        );
    }
}

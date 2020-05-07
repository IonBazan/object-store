<?php

declare(strict_types=1);

namespace App\Interfaces\Http;

use App\Domain\Model\ObjectEntry;
use App\Domain\Service\ObjectStoreInterface;
use DateTime;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/object")
 * @SWG\Tag(name="Object Store")
 */
class ApiController extends AbstractController
{
    protected ObjectStoreInterface $objectStorage;

    public function __construct(ObjectStoreInterface $objectStorage)
    {
        $this->objectStorage = $objectStorage;
    }

    /**
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Key-value pairs to store",
     *     @SWG\Schema(
     *         type="object",
     *         additionalProperties=true,
     *         example={"key": "value"}
     *     )
     * )
     * @SWG\Response(response="201", description="On success")
     * @SWG\Response(response="400", description="When provided object is not valid")
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
     * @SWG\Parameter(name="key", in="path", description="Key to display value for", type="string")
     * @SWG\Parameter(name="timestamp", in="query", description="Timestamp of the value snapshot", type="integer", required=false)
     * @SWG\Response(
     *     response="200",
     *     description="Value for the key at given time (or now)",
     *     @SWG\Schema(type="object", example="test-value")
     * )
     * @SWG\Response(response="400", description="When provided timestamp is invalid")
     * @SWG\Response(response="404", description="When key is not found (for provided time)")
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

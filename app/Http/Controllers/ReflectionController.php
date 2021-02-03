<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenDialogAi\Core\Reflection\Helper\ReflectionHelperInterface;

class ReflectionController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $reflectionHelper = resolve(ReflectionHelperInterface::class);

        return new JsonResponse([
            'action_engine' => $reflectionHelper->getActionEngineReflection(),
            'attribute_engine' => $reflectionHelper->getAttributeEngineReflection(),
            'context_engine' => $reflectionHelper->getContextEngineReflection(),
            'interpreter_engine' => $reflectionHelper->getInterpreterEngineReflection(),
            'operation_engine' => $reflectionHelper->getOperationEngineReflection(),
            'response_engine' => $reflectionHelper->getResponseEngineReflection(),
            'sensor_engine' => $reflectionHelper->getSensorEngineReflection(),
        ]);
    }
}

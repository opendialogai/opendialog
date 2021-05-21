<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComponentConfigurationRequest;
use App\Http\Requests\ComponentConfigurationTestRequest;
use App\Http\Resources\ComponentConfigurationCollection;
use App\Http\Resources\ComponentConfigurationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\InterpreterEngine\Service\InterpreterComponentServiceInterface;

class ComponentConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ComponentConfigurationCollection|Response
     */
    public function index()
    {
        return new ComponentConfigurationCollection(ComponentConfiguration::paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ComponentConfigurationRequest $request
     * @return ComponentConfigurationResource|Response
     */
    public function store(ComponentConfigurationRequest $request)
    {
        $configuration = ComponentConfiguration::create($request->all());

        return new ComponentConfigurationResource($configuration);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ComponentConfigurationResource|Response
     */
    public function show(int $id)
    {
        try {
            /** @var ComponentConfiguration $configuration */
            $configuration = ComponentConfiguration::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response(null, 404);
        }

        return new ComponentConfigurationResource($configuration);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ComponentConfigurationRequest $request
     * @param int $id
     * @return Response
     */
    public function update(ComponentConfigurationRequest $request, int $id)
    {
        try {
            /** @var ComponentConfiguration $configuration */
            $configuration = ComponentConfiguration::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response(null, 404);
        }

        $configuration->fill($request->all());
        $configuration->save();

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        try {
            /** @var ComponentConfiguration $configuration */
            $configuration = ComponentConfiguration::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response(null, 404);
        }

        $configuration->delete();

        return response()->noContent();
    }

    /**
     * Allows for testing of a configuration without persisting it
     *
     * @param ComponentConfigurationRequest $request
     * @return Response
     */
    public function test(ComponentConfigurationTestRequest $request): Response
    {
        $interpreterClass = resolve(InterpreterComponentServiceInterface::class)->get($request->get('component_id'));

        $utterance = new UtteranceAttribute('configuration_test');
        $utterance->setText("Hello from OpenDialog");
        $utterance->setCallbackId("test");

        $interpreter = new $interpreterClass($interpreterClass::createConfiguration('test', $request->get('configuration')));
        $intents = $interpreter->interpret($utterance);

        $status = $intents->isEmpty() ? 400 : 200;
        return response(null, $status);
    }
}

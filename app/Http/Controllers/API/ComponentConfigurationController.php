<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComponentConfigurationRequest;
use App\Http\Requests\ComponentConfigurationTestRequest;
use App\Http\Resources\ComponentConfigurationCollection;
use App\Http\Resources\ComponentConfigurationResource;
use Illuminate\Http\Response;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;

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
     * @param ComponentConfiguration $componentConfiguration
     * @return ComponentConfigurationResource|Response
     */
    public function show(ComponentConfiguration $componentConfiguration)
    {
        return new ComponentConfigurationResource($componentConfiguration);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ComponentConfigurationRequest $request
     * @param ComponentConfiguration $componentConfiguration
     * @return Response
     */
    public function update(ComponentConfigurationRequest $request, ComponentConfiguration $componentConfiguration)
    {
        $componentConfiguration->fill($request->all());
        $componentConfiguration->save();

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ComponentConfiguration $componentConfiguration
     * @return Response
     */
    public function destroy(ComponentConfiguration $componentConfiguration): Response
    {
        $componentConfiguration->delete();

        return response()->noContent();
    }

    /**
     * Allows for testing of a configuration without persisting it
     *
     * @param ComponentConfigurationTestRequest $request
     * @return Response
     */
    public function test(ComponentConfigurationTestRequest $request): Response
    {
        // todo: with configuration
        $interpreter = InterpreterService::getInterpreter($request->get('component_id'));

        $utterance = new UtteranceAttribute('configuration_test');
        $utterance->setText("Hello from OpenDialog");
        $utterance->setCallbackId("test");

        $intents = $interpreter->interpret($utterance);

        $status = $intents->isEmpty() ? 400 : 200;
        return response(null, $status);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComponentConfigurationQueryRequest;
use App\Http\Requests\ComponentConfigurationRequest;
use App\Http\Requests\ComponentConfigurationTestRequest;
use App\Http\Resources\ComponentConfigurationCollection;
use App\Http\Resources\ComponentConfigurationResource;
use App\Http\Resources\ScenarioResource;
use Illuminate\Http\Response;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
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
        $interpreterClass = resolve(InterpreterComponentServiceInterface::class)->get($request->get('component_id'));

        $utterance = new UtteranceAttribute('configuration_test');
        $utterance->setText("Hello from OpenDialog");
        $utterance->setCallbackId("test");

        $interpreter = new $interpreterClass($interpreterClass::createConfiguration('test', $request->get('configuration')));
        $intents = $interpreter->interpret($utterance);

        $status = $intents->isEmpty() ? 400 : 200;
        return response(null, $status);
    }

    /**
     * Allows for querying of a configuration across all conversation objects
     *
     * @param ComponentConfigurationQueryRequest $request
     * @return ScenarioResource|Response
     */
    public function query(ComponentConfigurationQueryRequest $request)
    {
        $scenarios = ConversationDataClient::getScenariosWhereInterpreterEquals($request->get('name'));

        return new ScenarioResource($scenarios);
    }
}

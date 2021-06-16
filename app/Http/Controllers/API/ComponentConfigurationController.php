<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComponentConfigurationQueryRequest;
use App\Http\Requests\ComponentConfigurationRequest;
use App\Http\Requests\ComponentConfigurationTestRequest;
use App\Http\Resources\ComponentConfigurationCollection;
use App\Http\Resources\ComponentConfigurationResource;
use App\Http\Resources\ScenarioResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Service\ActionComponentServiceInterface;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Components\Configuration\ComponentConfiguration;
use OpenDialogAi\Core\Components\Exceptions\UnknownComponentTypeException;
use OpenDialogAi\Core\Components\Helper\ComponentHelper;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Service\InterpreterComponentServiceInterface;

class ComponentConfigurationController extends Controller
{
    const ALL = 'all';
    const ACTION = 'action';
    const INTERPRETER = 'interpreter';

    const VALID_TYPES = [
        self::ALL,
        self::ACTION,
        self::INTERPRETER,
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ComponentConfigurationCollection|Response
     */
    public function index(Request $request)
    {
        $type = $request->get('type', self::ALL);

        switch ($type) {
            case self::ACTION:
                $configurations = ComponentConfiguration::actions()->paginate(50);
                break;
            case self::INTERPRETER:
                $configurations = ComponentConfiguration::interpreters()->paginate(50);
                break;
            case self::ALL:
            default:
                $configurations = ComponentConfiguration::paginate(50);
                break;
        }

        $configurations->appends(['type' => $type]);

        return new ComponentConfigurationCollection($configurations);
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
        $componentId = $request->get('component_id');

        try {
            $parsedComponentType = ComponentHelper::parseComponentId($componentId);
        } catch (UnknownComponentTypeException $e) {
            return response($e->getMessage(), 404);
        }

        switch ($parsedComponentType) {
            case ComponentHelper::INTERPRETER:
                return $this->testInterpreter($request);
            case ComponentHelper::ACTION:
                return $this->testAction($request);
            default:
                return response(null, 404);
        }
    }

    /**
     * Allows for querying of a configuration across all conversation objects
     *
     * @param ComponentConfigurationQueryRequest $request
     * @return ScenarioResource|Response
     */
    public function query(ComponentConfigurationQueryRequest $request)
    {
        $name = $request->get('name');

        /** @var ComponentConfiguration $configuration */
        $configuration = ComponentConfiguration::where('name', $name)->first();
        $componentId = $configuration->component_id;

        try {
            $parsedComponentType = ComponentHelper::parseComponentId($componentId);
        } catch (UnknownComponentTypeException $e) {
            return response($e->getMessage(), 404);
        }

        switch ($parsedComponentType) {
            case ComponentHelper::INTERPRETER:
                $scenarios = ConversationDataClient::getScenariosWhereInterpreterIsUsed($name);
                break;
            case ComponentHelper::ACTION:
                $scenarios = ConversationDataClient::getScenariosWhereActionIsUsed($name);
                break;
            default:
                return response(null, 404);
        }

        return new ScenarioResource($scenarios);
    }

    /**
     * @param ComponentConfigurationTestRequest $request
     * @return Response
     */
    private function testInterpreter(ComponentConfigurationTestRequest $request): Response
    {
        $data = null;
        $text = $request->get('utterance') ?? "Hello from OpenDialog";

        try {
            $interpreterClass = resolve(InterpreterComponentServiceInterface::class)->get($request->get('component_id'));

            $utterance = new UtteranceAttribute('configuration_test');
            $utterance->setText($text);
            $utterance->setCallbackId("test");

            $configuration = $interpreterClass::createConfiguration('test', $request->get('configuration'));
            $interpreter = new $interpreterClass($configuration);

            /** @var IntentCollection $intents */
            $intents = $interpreter->interpret($utterance);

            $status = $intents->isEmpty() ? 400 : 200;

            if ($intents->isEmpty()) {
                $data = [
                    'errors' => [
                        'no-match' => [
                            sprintf(
                                "No intent found for the utterance: '%s'. Perhaps try a different utterance.",
                                $text
                            )
                        ]
                    ]
                ];
            } else {
                /** @var Intent $intent */
                $intent = $intents->first();
                $data = [
                    'messages' => [
                        'intent' => [
                            sprintf(
                                "Utterance '%s' interpreted as intent '%s' with confidence %d%%.",
                                $text,
                                $intent->getOdId(),
                                $intent->getConfidence() * 100
                            )
                        ]
                    ]
                ];
            }
        } catch (Exception $e) {
            Log::info(sprintf(
                "Testing interpreter (%s) failed, caught exception: %s",
                $request->get('component_id'),
                $e->getMessage()
            ));

            $status = 400;

            $data = [
                'errors' => [
                    'exception' => [$e->getMessage()]
                ]
            ];
        }
        return response($data, $status);
    }

    /**
     * @param ComponentConfigurationTestRequest $request
     * @return Response
     */
    private function testAction(ComponentConfigurationTestRequest $request): Response
    {
        try {
            $actionClass = resolve(ActionComponentServiceInterface::class)->get($request->get('component_id'));

            /** @var ActionInterface $action */
            $action = new $actionClass($actionClass::createConfiguration('test', $request->get('configuration')));
            $result = $action->perform(new ActionInput());

            $status = $result->isSuccessful() ? 200 : 400;
        } catch (Exception $e) {
            Log::info(sprintf(
                'Running test on action with component ID %s ran into and exception and failed - %s',
                $request->get('component_id'),
                $e->getMessage()
            ));
            $status = 400;
        }
        return response(null, $status);
    }
}

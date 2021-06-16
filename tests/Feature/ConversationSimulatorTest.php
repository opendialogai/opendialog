<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Util\ConversationalState;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class ConversationSimulatorTest extends TestCase
{
    public function testSuccess()
    {
        $this->mockSelectors();

        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->json('POST', '/admin/api/conversation-simulation', [
                "scenario" => null,
                "conversation" => null,
                "scene" => null,
                "turn" => null,
                "intent" => null,
                "speaker" => Intent::APP,
                "turn_status" => ConversationalState::OUT_OF_TURN,
            ])
            ->assertStatus(200);
    }

    public function testFailure()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->json('POST', '/admin/api/conversation-simulation', [
                "scenario" => null,
                "conversation" => null,
                "scene" => null,
                "turn" => null,
                "intent" => null,
                "speaker" => Intent::APP,
                "turn_status" => 'unknown',
            ])
            ->assertStatus(422);
    }

    protected function mockSelectors(): void
    {
        ScenarioSelector::shouldReceive('selectScenarios')
            ->once()
            ->andReturn(new ScenarioCollection());
        ScenarioSelector::makePartial();

        ConversationSelector::shouldReceive('selectStartingConversations')
            ->once()
            ->andReturn(new ConversationCollection());
        ConversationSelector::makePartial();

        SceneSelector::shouldReceive('selectStartingScenes')
            ->once()
            ->andReturn(new SceneCollection());
        SceneSelector::makePartial();

        TurnSelector::shouldReceive('selectStartingTurns')
            ->once()
            ->andReturn(new TurnCollection());
        TurnSelector::makePartial();

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn(new IntentCollection());
        IntentSelector::makePartial();
    }
}

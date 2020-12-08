<?php

return [
    /**
     * A registration of know LUIS entities mapped to known attribute type. If an entity is returned from LUIS that is
     * not an already registered attribute name and is not mapped here, a StringAttribute will be used
     *
     * Mapping is {luis_entity_type} => {OD_attribute_name}
     */
    'luis_entities' => [
//         'example_type' => 'first_name'
    ],

    'dialogflow_config' => [
        'project_ids' => [
            'agent_1' => env('DIALOGFLOW_AGENT_1_PROJECT_ID'),
            'agent_2' => env('DIALOGFLOW_AGENT_2_PROJECT_ID'),
        ],
        'credentials' => [
            env('DIALOGFLOW_AGENT_1_PROJECT_ID') => env('DIALOGFLOW_AGENT_1_CREDENTIALS'),
            env('DIALOGFLOW_AGENT_2_PROJECT_ID') => env('DIALOGFLOW_AGENT_2_CREDENTIALS'),
            '_fallback' => env('DIALOGFLOW_FALLBACK_CREDENTIALS'),
        ],
        'languageCodes' => [
            'agent_1' => 'en-GB',
            'agent_2' => 'en-GB'
        ]
    ],

    /**
     * Custom interpreters registered in the format
     */
    'custom_interpreters' => [
        \App\Bot\Interpreter\Agent1DialogflowInterpreter::class,
        \App\Bot\Interpreter\Agent2DialogflowInterpreter::class,
    ],

    'default_interpreter' => 'interpreter.core.callbackInterpreter',

    /**
     * List of supported intents in the format 'callback_id' => 'intent_name'
     */
    'supported_callbacks' => [
        'WELCOME' => 'intent.core.welcome',
        'list_message' => 'intent.core.TestListMessage'
    ]
];

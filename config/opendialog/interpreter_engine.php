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
        'interpreter.core.dialogflow' => [
            'project_id' => env('DIALOGFLOW_PROJECT_ID'),
            'intents' => [
                'Knowledge.KnowledgeBase.*' => 'intent.dialogflow.faq',
            ],
            'entities' => [],
        ],
    ],

    /**
     * Custom interpreters registered in the format
     */
    'custom_interpreters' => [
//    \OpenDialogAi\InterpreterEngine\tests\Interpreters\DummyInterpreter::class
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

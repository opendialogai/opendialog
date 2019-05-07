<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @yield('scripts', '')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BDO OpenDialog Demo Page</title>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div id="app">
        <h2>Send Trigger message</h2>
        <form onsubmit="sendTriggerMessage(); return false;">
            <div>
                <label>Callback id:</label>
                <input id="callback_id" />
            </div>
            <div>
                <label>Value:</label>
                <input id="value" />
            </div>
            <button>Send trigger message</button>
        </form>

        <hr/>

        <h2>Set custom user attribute</h2>
        <form onsubmit="updateCustomAttributes(); return false;">
            <div>
                <label>Attribute ID:</label>
                <input id="attribute" />
            </div>
            <div>
                <label>Value:</label>
                <input id="value" />
            </div>
            <button>Update User attribute</button>
        </form>
    </div>

    <script>
        window.openDialogSettings = {
            url: "{{ env("APP_URL") }}",
            teamName: 'LISA',
            user: {
                first_name: 'Stuart',
                last_name: 'Haigh',
                email: 'stuarth@greenshootlabs.com',
                external_id: '1',
            },
        };

        const sendTriggerMessage = function() {
            const callback_id = document.getElementById('callback_id').value;
            const value = document.getElementById('value').value;

            if (callback_id == '') {
                alert('Insert a not empty "Callback id" value.');
            } else {
                if (value == '') {
                    document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
                        triggerConversation: {
                            callback_id,
                        },
                    });
                } else {
                    document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
                        triggerConversation: {
                            callback_id,
                            value,
                        },
                    });
                }
            }
        }

        const updateCustomAttributes = function() {
          const attributeName = document.getElementById('attribute').value;
          const value = document.getElementById('value').value;

            document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
              customUserSettings: {
                [attributeName]: value
              },
            });

        }
    </script>

    <script src="{{env('APP_URL')}}/vendor/webchat/js/opendialog-bot.js"></script>
</body>
</html>

<template>
  <div>
    <b-card header="Webchat settings">
      <form @submit.prevent="updateWebchatSettings">
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">URL</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="url" />
          </div>
        </div>
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">Team Name</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="teamName" />
          </div>
        </div>
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">User First Name</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="userFirstName" />
          </div>
        </div>
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">User Last Name</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="userLastName" />
          </div>
        </div>
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">User Email</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="userEmail" />
          </div>
        </div>
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">User External ID</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="userExternalId" />
          </div>
        </div>
        <button class="btn btn-primary">Update webchat settings</button>
      </form>
    </b-card>

    <b-card header="Send Trigger message">
      <form @submit.prevent="sendTriggerMessage">
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">Callback id</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="callbackId" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-1 col-form-label">Value</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="triggerValue" />
          </div>
        </div>
        <button class="btn btn-primary">Send trigger message</button>
      </form>
    </b-card>

    <b-card header="Set custom user attribute">
      <form @submit.prevent="updateCustomAttributes">
        <div class="form-group mb-2 row">
          <label class="col-sm-1 col-form-label">Attribute ID</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="attributeName" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-1 col-form-label">Value</label>
          <div class="col-sm-11">
            <input class="form-control" v-model="attributeValue" />
          </div>
        </div>
        <button class="btn btn-primary">Update User attribute</button>
      </form>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'webchat-demo',
  data() {
    return {
      attributeName: '',
      attributeValue: '',
      callbackId: '',
      teamName: window.openDialogSettings.general.teamName,
      triggerValue: '',
      url: window.openDialogSettings.url,
      userEmail: window.openDialogSettings.user.email,
      userExternalId: window.openDialogSettings.user.external_id,
      userFirstName: window.openDialogSettings.user.first_name,
      userLastName: window.openDialogSettings.user.last_name,
    };
  },
  mounted() {
    if (localStorage.getItem('reloaded')) {
      localStorage.removeItem('reloaded');
    } else {
      localStorage.setItem('reloaded', '1');
      window.location.reload();
    }
  },
  methods: {
    sendTriggerMessage() {
      const callbackId = this.callbackId;
      const triggerValue = this.triggerValue;

      if (callbackId == '') {
        alert('Insert a not empty "Callback id" value.');
      } else {
        if (triggerValue == '') {
          document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
            triggerConversation: {
              callback_id: callbackId,
            },
          });
        } else {
          document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
            triggerConversation: {
              callback_id: callbackId,
              value: triggerValue,
            },
          });
        }
      }
    },
    updateCustomAttributes() {
      const attributeName = this.attributeName;
      const attributeValue = this.attributeValue;

      document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
        customUserSettings: {
          [attributeName]: attributeValue,
        },
      });
    },
    updateWebchatSettings() {
      const teamName = this.teamName;
      const userEmail = this.userEmail;
      const userExternalId = this.userExternalId;
      const userFirstName = this.userFirstName;
      const userLastName = this.userLastName;
      const url = this.url;

      document.querySelector('#opendialog-chatwindow').contentWindow.postMessage({
        openDialogSettings: {
          url,
          general: {
            teamName,
          },
          user: {
            first_name: userFirstName,
            last_name: userLastName,
            email: userEmail,
            external_id: userExternalId,
          },
        },
      });
    },
  },
  beforeDestroy() {
    window.location.reload();
  },
};
</script>

<script>
import axios from 'axios';
import Modal from '@nextcloud/vue/dist/Components/Modal';
import EventBus from './EventBus';
import { generateUrl } from '@nextcloud/router';
import queryString from 'query-string';
import OC from './OC';

export default {
  name: 'SignatureLinkModal',
  components: {
    Modal,
  },
  data() {
    return {
      modal: false,
      errorMessage: null,
      successMessage: null,
      isLoading: false,
      adminSettings: null,
      email: '',
      filename: '',
    };
  },
  mounted() {
    const _self = this;
    EventBus.$on('SIGNATURES_CLICK', function(payload) {
      _self.showModal();
      _self.filename = payload.filename;
    });
  },
  computed: {
    fileExtension() {
      return this.filename.split('.').pop();
    }
  },
  methods: {
    generateNextcloudUrl(url) {
      return generateUrl(url);
    },
    showModal() {
      if (!this.adminSettings) {
        this.fetchAdminSettings();
      }
      this.setErrorMessage(null);
      this.setSuccessMessage(null);
      this.modal = true;
    },
    closeModal() {
      this.modal = false;
    },
    setErrorMessage(message) {
      if (message === null) {
        this.errorMessage = null;
      } else if (!message) {
        this.errorMessage = this.$t(this.$globalConfig.appId, 'Something went wrong. Make sure that the electronic signatures app settings are correct.');
      } else {
        this.errorMessage = message;
      }
    },
    setSuccessMessage(message) {
      this.successMessage = message;
    },
    getFilePath() {
      const parsed = queryString.parse(window.location.search);
      if (parsed.dir === '/') {
        return this.filename;
      } else {
        return parsed.dir + '/' + this.filename;
      }
    },
    setAdminSettings(settings) {
      this.adminSettings = settings;
    },
    fetchAdminSettings() {
      const _self = this;
      axios({
        method: 'get',
        url: this.generateNextcloudUrl('/apps/electronicsignatures/settings'),
        responseType: 'json',
        headers: {
          requesttoken: OC.requestToken,
        },
      })
          .then(function(response) {
            _self.setAdminSettings(response.data);
          });
    },
    onSubmit() {
      const _self = this;
      this.isLoading = true;
      this.setErrorMessage(null);
      this.setSuccessMessage(null);
      axios({
        method: 'post',
        url: generateUrl('/apps/electronicsignatures/send_sign_link_by_email'),
        responseType: 'json',
        headers: {
          requesttoken: OC.requestToken,
        },
        data: {
          path: this.getFilePath(),
          email: this.email,
        },
      })
        .then(function() {
          _self.setSuccessMessage(_self.$t(_self.$globalConfig.appId, 'Email successfully sent!'));
        })
        .catch(function(error) {
          _self.setErrorMessage(error.response && error.response.data && error.response.data.message);
        })
        .then(function() {
          _self.isLoading = false;
        });
    },
  },
};
</script>

<template>
  <div>
    <modal
        v-if="modal"
        @close="closeModal">
      <div
        class="modal__content">
        <div
            v-if="isLoading"
             class="loader">
          <div class="icon-loading spinner" />
        </div>
        <div class="contentWrap">
          <h3>
            {{ $t($globalConfig.appId, 'Send the signing link by email') }}
          </h3>

          <div v-if="adminSettings && adminSettings.enable_otp && fileExtension !== 'pdf'">
            <span class="alert alert-warning">
              {{ $t($globalConfig.appId, 'You have enabled simple signatures, but this file is not a pdf. User cannot add a simple electronic signature to this file.') }}
            </span>
          </div>

          <div v-if="errorMessage">
            <span class="alert alert-danger">{{ errorMessage }}</span>
          </div>

          <div v-if="successMessage">
            <span class="alert alert-success">{{ successMessage }}</span>
          </div>

          <form
              action=""
              @submit.prevent="onSubmit">
            <label
                class="label"
                for="signingLinkEmail">
              {{ $t($globalConfig.appId, 'Enter the email address of the person who should sign this document.') }}
            </label>
            <div class="fieldRow">
              <input
                  id="signingLinkEmail"
                  v-model="email"
                  type="email"
                  class="input"
                  placeholder="Email"
                  required
                  aria-label="email">
              <button
                  class="submitButton"
                  type="submit">
                {{ $t($globalConfig.appId, 'Send') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </modal>
  </div>
</template>

<style scoped>
  .modal__content {
    width: 68vw;
    max-width: 600px;
    padding: 2rem 1rem;
    box-sizing: border-box;
    position: relative;
  }

  .modal__content * {
    box-sizing: border-box;
  }

  .loader {
    margin: 0 auto;
    position: absolute;
    z-index: 500;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: rgba(255, 255, 255, 0.9);
  }

  .contentWrap {
    position: relative;
  }

  .error {
    color: #842029;
  }

  h3 {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: bold;
  }

  .fieldRow {
    display: flex;
    margin-bottom: 1rem;
  }

  .label {
    display: block;
    margin-bottom: 10px;
  }

  .input {
    width: 100%;
    max-width: 300px;
  }

  .spinner {
    top: 50%;
  }

  .alert {
    display: block;
    position: relative;
    padding: .75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: .25rem;
  }

  .alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
  }

  .alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
  }

  .alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
  }

  @media (min-width: 600px) {
    .modal__content {
      padding: 2rem;
    }
  }
</style>

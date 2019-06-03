import axios from 'axios';
import store from './store/index';

const $http = () => {
    axios.interceptors.response.use(response => {
        store.dispatch('response/set', response);

        return response;
    }, (error) => {
        store.dispatch('response/set', error.response);

        return error;
    });

    return axios;
};

export default $http;
import CreateOrder from './components/CreateOrderComponent';

vueApp.booting(vue => {
    vue.filter('formatPrice', value => {
        return parseFloat(value).toFixed(2);
    });

    vue.component('create-order', CreateOrder);
});

// Run with NODE_PATH pointing at the isolated build dependencies; no browser/network.
const { JSDOM } = require('jsdom')
const fs = require('node:fs')
const path = require('node:path')
const assert = require('node:assert/strict')
const root = path.resolve(__dirname, '../../../..')
const dom = new JSDOM(`<form class="checkout-form" data-update-url="/quote">
<div class="customer-address-payment-form"><div class="address-form-wrapper">
<input class="form-control" required name="address[name]" id="address_name">
<input class="form-control" name="address[zip_code]" id="address_zip_code" value="313002">
</div></div>
<input type="radio" name="payment_method" value="razorpay" checked>
<div id="main-checkout-product-info"><div class="cart-item-wrapper"></div></div>
<div data-bb-toggle="checkout-payment-methods-area"></div><div data-bb-toggle="checkout-shipping-methods-area"></div>
<button class="payment-checkout-btn" type="submit">Checkout</button>
</form>`, {url:'https://test.invalid/checkout/test',runScripts:'outside-only'})
const w = dom.window
w.eval(fs.readFileSync(path.join(path.dirname(require.resolve('jquery')), 'jquery.js'),'utf8'))
const $ = w.$
$.fn.valid = () => false // Shipping must still refresh with incomplete contact details.
const requests = []
$.ajax = options => {requests.push(options); options.beforeSend?.(); return {}}
w.eval(fs.readFileSync(root+'/public/vendor/core/plugins/shipmozo/js/checkout.js','utf8'))
const wait = ms => new Promise(r=>setTimeout(r,ms))
const markup = (id,disabled=false) => `<input class="shipping_method_input" type="radio" name="shipping_method[12]" value="shipmozo" id="${id}" checked ${disabled?'disabled':''}><span class="shipping-price-text">87</span>`
const respond = (request,id,disabled=false) => { request.success({data:{amount:markup(id,disabled),payment_methods:'',shipping_methods:''}}); request.complete() }
;(async()=>{
 await wait(50)
 assert.equal(requests.length,1,'one initial quote')
 $('#address_zip_code').val('313001').trigger('input')
 $('#address_zip_code').val('313003').trigger('input')
 assert.equal($('.payment-checkout-btn').prop('disabled'),true,'blocked immediately during debounce')
 await wait(400)
 assert.equal(requests.length,1,'no parallel quote/session writes')
 respond(requests[0],'stale')
 assert.equal($('#stale').length,0,'stale response discarded')
 assert.equal(requests.length,2,'latest request follows old completion')
 assert.equal(requests[1].data.get('address[zip_code]'),'313003','latest destination sent without contact fields')
 respond(requests[1],'current')
 assert.equal($('#current').length,1)
 assert.equal($('.payment-checkout-btn').prop('disabled'),false,'ready only after latest valid quote')
 $('#address_zip_code').val('000000').trigger('change')
 await wait(400)
 respond(requests[2],'no-service',true)
 assert.equal($('.payment-checkout-btn').prop('disabled'),true,'disabled courier cannot proceed')
 const submit=new w.Event('submit',{cancelable:true,bubbles:true}); w.document.querySelector('form').dispatchEvent(submit)
 assert.equal(submit.defaultPrevented,true,'enter-key submit blocked for unavailable quote')
 assert.equal($('.shipping-price-text').text(),'Pending shipping confirmation')
 $('#address_zip_code').val('313002').trigger('input')
 await wait(400)
 requests[3].error(); requests[3].complete()
 assert.equal($('.payment-checkout-btn').prop('disabled'),true,'network failure cannot enable old quote')
 $('.checkout-shipping-status button').trigger('click')
 assert.equal(requests.length,5,'retry creates a fresh quote')
 respond(requests[4],'recovered')
 assert.equal($('.payment-checkout-btn').prop('disabled'),false,'successful retry recovers checkout')
 console.log('PASS initial/incomplete form, latest destination, serialized quotes, stale response, invalid PIN, submit guard, failure and retry')
 w.close()
})().catch(e=>{console.error(e);w.close();process.exitCode=1})

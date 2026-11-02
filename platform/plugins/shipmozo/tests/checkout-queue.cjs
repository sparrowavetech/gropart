const {JSDOM}=require('jsdom');
const fs=require('fs');
const assert=require('node:assert/strict');
(async()=>{
 const dom=new JSDOM(`<form class="checkout-form" data-update-url="/quote"><input id="save-shipping-information-url" value="/save"><div class="customer-address-payment-form"><div class="address-form-wrapper"><input id="address_zip_code" class="form-control" name="address[zip_code]" value="313001" required><input id="address_name" class="form-control" name="address[name]" value="QA" required></div></div><input type="radio" name="payment_method" value="razorpay" checked><div id="main-checkout-product-info"><input class="shipping_method_input" type="radio" name="shipping_method[1]" value="shipmozo" checked></div><button class="payment-checkout-btn">Checkout</button><div class="cart-item-wrapper"></div><div data-bb-toggle="checkout-payment-methods-area"></div><div data-bb-toggle="checkout-shipping-methods-area"></div></form>`,{url:'http://localhost',runScripts:'outside-only'});
 const w=dom.window,$=require('jquery')(w); w.$=w.jQuery=$; $.fn.valid=()=>true; let calls=[]; $.ajax=o=>{calls.push(o);return {}};
 w.eval(fs.readFileSync('platform/plugins/shipmozo/public/js/checkout.js','utf8'));
 const pause=()=>new Promise(r=>setTimeout(r,450));
 await pause();
 const respond=o=>{o.success?.({error:false,data:{shipping_methods:'<input class="shipping_method_input" type="radio" name="shipping_method[1]" value="shipmozo" checked>',payment_methods:'<input type="radio" name="payment_method" value="razorpay" checked>',amount:'OK'}});o.complete?.()};
 while(calls.length)respond(calls.shift());
 $('#address_name').val('QA changed').trigger('change'); await pause();
 assert.equal(calls.length,1);assert.equal(calls[0].url,'/save');
 $('#address_zip_code').val('313002').trigger('input');await pause();assert.equal(calls.length,1,'quote must wait for address save');
 respond(calls.shift());assert.equal(calls.length,1);assert.equal(calls[0].url,'/quote');assert.equal(calls[0].data.get('address[zip_code]'),'313002');respond(calls.shift());
 w.document.dispatchEvent(new w.CustomEvent('coupon:applied'));assert.equal(calls.length,1);assert.equal(calls[0].data.get('payment_method'),'razorpay');assert.equal(calls[0].data.has('length'),false,'no jQuery object fields in request');respond(calls.shift());
 $('#address_zip_code').val('313001').trigger('input');await pause();const old=calls.shift();$('#address_zip_code').val('313002').trigger('input');respond(old);assert.equal(calls.length,1);assert.equal(calls[0].data.get('address[zip_code]'),'313002');respond(calls.shift());
 dom.window.close();console.log('PASS address-save/quote serialization, latest PIN, stale response queue, coupon payment payload');
})().catch(e=>{console.error(e);process.exit(1)});

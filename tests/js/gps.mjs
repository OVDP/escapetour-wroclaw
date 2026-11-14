import assert from 'node:assert/strict';
import {getFreshPosition, gpsErrorMessage} from '../../public/js/gps.mjs';
await assert.rejects(getFreshPosition({isSecureContext:false}), /HTTPS/);
await assert.rejects(getFreshPosition({isSecureContext:true,navigator:{}}), /ondersteunt/);
await assert.rejects(getFreshPosition({isSecureContext:true,navigator:{geolocation:{}},document:{permissionsPolicy:{allowsFeature:()=>false}}}), /serverinstellingen/);
const pos = {coords:{latitude:51,longitude:17,accuracy:20},timestamp:Date.now()};
const browser = {isSecureContext:true,navigator:{geolocation:{getCurrentPosition:(success,error,options)=>{
    assert.equal(options.maximumAge,0); assert.equal(options.enableHighAccuracy,true); success(pos);
}}}};
assert.equal(await getFreshPosition(browser),pos);
pos.timestamp=Date.now()-180000;
await assert.rejects(getFreshPosition(browser), /verouderd/);
for (const code of [1,2,3]) assert.notEqual(gpsErrorMessage({code}),gpsErrorMessage({code:99}));
assert.equal(gpsErrorMessage(new Error('Sessie verlopen')), 'Sessie verlopen');
console.log('GPS: HTTPS, serverbeleid, verse metingen en foutmeldingen gecontroleerd.');

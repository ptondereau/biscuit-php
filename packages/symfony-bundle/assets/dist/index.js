/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const t$3=window,e$6=t$3.ShadowRoot&&(void 0===t$3.ShadyCSS||t$3.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,s$3=Symbol(),n$5=new WeakMap;class o$4{constructor(t,e,n){if(this._$cssResult$=!0,n!==s$3)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=t,this.t=e;}get styleSheet(){let t=this.o;const s=this.t;if(e$6&&void 0===t){const e=void 0!==s&&1===s.length;e&&(t=n$5.get(s)),void 0===t&&((this.o=t=new CSSStyleSheet).replaceSync(this.cssText),e&&n$5.set(s,t));}return t}toString(){return this.cssText}}const r$2=t=>new o$4("string"==typeof t?t:t+"",void 0,s$3),i$3=(t,...e)=>{const n=1===t.length?t[0]:e.reduce(((e,s,n)=>e+(t=>{if(!0===t._$cssResult$)return t.cssText;if("number"==typeof t)return t;throw Error("Value passed to 'css' function must be a 'css' function result: "+t+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(s)+t[n+1]),t[0]);return new o$4(n,t,s$3)},S$1=(s,n)=>{e$6?s.adoptedStyleSheets=n.map((t=>t instanceof CSSStyleSheet?t:t.styleSheet)):n.forEach((e=>{const n=document.createElement("style"),o=t$3.litNonce;void 0!==o&&n.setAttribute("nonce",o),n.textContent=e.cssText,s.appendChild(n);}));},c$1=e$6?t=>t:t=>t instanceof CSSStyleSheet?(t=>{let e="";for(const s of t.cssRules)e+=s.cssText;return r$2(e)})(t):t;

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var s$2;const e$5=window,r$1=e$5.trustedTypes,h$1=r$1?r$1.emptyScript:"",o$3=e$5.reactiveElementPolyfillSupport,n$4={toAttribute(t,i){switch(i){case Boolean:t=t?h$1:null;break;case Object:case Array:t=null==t?t:JSON.stringify(t);}return t},fromAttribute(t,i){let s=t;switch(i){case Boolean:s=null!==t;break;case Number:s=null===t?null:Number(t);break;case Object:case Array:try{s=JSON.parse(t);}catch(t){s=null;}}return s}},a$1=(t,i)=>i!==t&&(i==i||t==t),l$3={attribute:!0,type:String,converter:n$4,reflect:!1,hasChanged:a$1},d$1="finalized";class u$1 extends HTMLElement{constructor(){super(),this._$Ei=new Map,this.isUpdatePending=!1,this.hasUpdated=!1,this._$El=null,this._$Eu();}static addInitializer(t){var i;this.finalize(),(null!==(i=this.h)&&void 0!==i?i:this.h=[]).push(t);}static get observedAttributes(){this.finalize();const t=[];return this.elementProperties.forEach(((i,s)=>{const e=this._$Ep(s,i);void 0!==e&&(this._$Ev.set(e,s),t.push(e));})),t}static createProperty(t,i=l$3){if(i.state&&(i.attribute=!1),this.finalize(),this.elementProperties.set(t,i),!i.noAccessor&&!this.prototype.hasOwnProperty(t)){const s="symbol"==typeof t?Symbol():"__"+t,e=this.getPropertyDescriptor(t,s,i);void 0!==e&&Object.defineProperty(this.prototype,t,e);}}static getPropertyDescriptor(t,i,s){return {get(){return this[i]},set(e){const r=this[t];this[i]=e,this.requestUpdate(t,r,s);},configurable:!0,enumerable:!0}}static getPropertyOptions(t){return this.elementProperties.get(t)||l$3}static finalize(){if(this.hasOwnProperty(d$1))return !1;this[d$1]=!0;const t=Object.getPrototypeOf(this);if(t.finalize(),void 0!==t.h&&(this.h=[...t.h]),this.elementProperties=new Map(t.elementProperties),this._$Ev=new Map,this.hasOwnProperty("properties")){const t=this.properties,i=[...Object.getOwnPropertyNames(t),...Object.getOwnPropertySymbols(t)];for(const s of i)this.createProperty(s,t[s]);}return this.elementStyles=this.finalizeStyles(this.styles),!0}static finalizeStyles(i){const s=[];if(Array.isArray(i)){const e=new Set(i.flat(1/0).reverse());for(const i of e)s.unshift(c$1(i));}else void 0!==i&&s.push(c$1(i));return s}static _$Ep(t,i){const s=i.attribute;return !1===s?void 0:"string"==typeof s?s:"string"==typeof t?t.toLowerCase():void 0}_$Eu(){var t;this._$E_=new Promise((t=>this.enableUpdating=t)),this._$AL=new Map,this._$Eg(),this.requestUpdate(),null===(t=this.constructor.h)||void 0===t||t.forEach((t=>t(this)));}addController(t){var i,s;(null!==(i=this._$ES)&&void 0!==i?i:this._$ES=[]).push(t),void 0!==this.renderRoot&&this.isConnected&&(null===(s=t.hostConnected)||void 0===s||s.call(t));}removeController(t){var i;null===(i=this._$ES)||void 0===i||i.splice(this._$ES.indexOf(t)>>>0,1);}_$Eg(){this.constructor.elementProperties.forEach(((t,i)=>{this.hasOwnProperty(i)&&(this._$Ei.set(i,this[i]),delete this[i]);}));}createRenderRoot(){var t;const s=null!==(t=this.shadowRoot)&&void 0!==t?t:this.attachShadow(this.constructor.shadowRootOptions);return S$1(s,this.constructor.elementStyles),s}connectedCallback(){var t;void 0===this.renderRoot&&(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),null===(t=this._$ES)||void 0===t||t.forEach((t=>{var i;return null===(i=t.hostConnected)||void 0===i?void 0:i.call(t)}));}enableUpdating(t){}disconnectedCallback(){var t;null===(t=this._$ES)||void 0===t||t.forEach((t=>{var i;return null===(i=t.hostDisconnected)||void 0===i?void 0:i.call(t)}));}attributeChangedCallback(t,i,s){this._$AK(t,s);}_$EO(t,i,s=l$3){var e;const r=this.constructor._$Ep(t,s);if(void 0!==r&&!0===s.reflect){const h=(void 0!==(null===(e=s.converter)||void 0===e?void 0:e.toAttribute)?s.converter:n$4).toAttribute(i,s.type);this._$El=t,null==h?this.removeAttribute(r):this.setAttribute(r,h),this._$El=null;}}_$AK(t,i){var s;const e=this.constructor,r=e._$Ev.get(t);if(void 0!==r&&this._$El!==r){const t=e.getPropertyOptions(r),h="function"==typeof t.converter?{fromAttribute:t.converter}:void 0!==(null===(s=t.converter)||void 0===s?void 0:s.fromAttribute)?t.converter:n$4;this._$El=r,this[r]=h.fromAttribute(i,t.type),this._$El=null;}}requestUpdate(t,i,s){let e=!0;void 0!==t&&(((s=s||this.constructor.getPropertyOptions(t)).hasChanged||a$1)(this[t],i)?(this._$AL.has(t)||this._$AL.set(t,i),!0===s.reflect&&this._$El!==t&&(void 0===this._$EC&&(this._$EC=new Map),this._$EC.set(t,s))):e=!1),!this.isUpdatePending&&e&&(this._$E_=this._$Ej());}async _$Ej(){this.isUpdatePending=!0;try{await this._$E_;}catch(t){Promise.reject(t);}const t=this.scheduleUpdate();return null!=t&&await t,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var t;if(!this.isUpdatePending)return;this.hasUpdated,this._$Ei&&(this._$Ei.forEach(((t,i)=>this[i]=t)),this._$Ei=void 0);let i=!1;const s=this._$AL;try{i=this.shouldUpdate(s),i?(this.willUpdate(s),null===(t=this._$ES)||void 0===t||t.forEach((t=>{var i;return null===(i=t.hostUpdate)||void 0===i?void 0:i.call(t)})),this.update(s)):this._$Ek();}catch(t){throw i=!1,this._$Ek(),t}i&&this._$AE(s);}willUpdate(t){}_$AE(t){var i;null===(i=this._$ES)||void 0===i||i.forEach((t=>{var i;return null===(i=t.hostUpdated)||void 0===i?void 0:i.call(t)})),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(t)),this.updated(t);}_$Ek(){this._$AL=new Map,this.isUpdatePending=!1;}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$E_}shouldUpdate(t){return !0}update(t){void 0!==this._$EC&&(this._$EC.forEach(((t,i)=>this._$EO(i,this[i],t))),this._$EC=void 0),this._$Ek();}updated(t){}firstUpdated(t){}}u$1[d$1]=!0,u$1.elementProperties=new Map,u$1.elementStyles=[],u$1.shadowRootOptions={mode:"open"},null==o$3||o$3({ReactiveElement:u$1}),(null!==(s$2=e$5.reactiveElementVersions)&&void 0!==s$2?s$2:e$5.reactiveElementVersions=[]).push("1.6.3");

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
var t$2;const i$2=window,s$1=i$2.trustedTypes,e$4=s$1?s$1.createPolicy("lit-html",{createHTML:t=>t}):void 0,o$2="$lit$",n$3=`lit$${(Math.random()+"").slice(9)}$`,l$2="?"+n$3,h=`<${l$2}>`,r=document,u=()=>r.createComment(""),d=t=>null===t||"object"!=typeof t&&"function"!=typeof t,c=Array.isArray,v=t=>c(t)||"function"==typeof(null==t?void 0:t[Symbol.iterator]),a="[ \t\n\f\r]",f=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,_=/-->/g,m=/>/g,p=RegExp(`>|${a}(?:([^\\s"'>=/]+)(${a}*=${a}*(?:[^ \t\n\f\r"'\`<>=]|("|')|))|$)`,"g"),g=/'/g,$=/"/g,y=/^(?:script|style|textarea|title)$/i,w=t=>(i,...s)=>({_$litType$:t,strings:i,values:s}),x=w(1),T=Symbol.for("lit-noChange"),A=Symbol.for("lit-nothing"),E=new WeakMap,C=r.createTreeWalker(r,129,null,!1);function P(t,i){if(!Array.isArray(t)||!t.hasOwnProperty("raw"))throw Error("invalid template strings array");return void 0!==e$4?e$4.createHTML(i):i}const V=(t,i)=>{const s=t.length-1,e=[];let l,r=2===i?"<svg>":"",u=f;for(let i=0;i<s;i++){const s=t[i];let d,c,v=-1,a=0;for(;a<s.length&&(u.lastIndex=a,c=u.exec(s),null!==c);)a=u.lastIndex,u===f?"!--"===c[1]?u=_:void 0!==c[1]?u=m:void 0!==c[2]?(y.test(c[2])&&(l=RegExp("</"+c[2],"g")),u=p):void 0!==c[3]&&(u=p):u===p?">"===c[0]?(u=null!=l?l:f,v=-1):void 0===c[1]?v=-2:(v=u.lastIndex-c[2].length,d=c[1],u=void 0===c[3]?p:'"'===c[3]?$:g):u===$||u===g?u=p:u===_||u===m?u=f:(u=p,l=void 0);const w=u===p&&t[i+1].startsWith("/>")?" ":"";r+=u===f?s+h:v>=0?(e.push(d),s.slice(0,v)+o$2+s.slice(v)+n$3+w):s+n$3+(-2===v?(e.push(void 0),i):w);}return [P(t,r+(t[s]||"<?>")+(2===i?"</svg>":"")),e]};class N{constructor({strings:t,_$litType$:i},e){let h;this.parts=[];let r=0,d=0;const c=t.length-1,v=this.parts,[a,f]=V(t,i);if(this.el=N.createElement(a,e),C.currentNode=this.el.content,2===i){const t=this.el.content,i=t.firstChild;i.remove(),t.append(...i.childNodes);}for(;null!==(h=C.nextNode())&&v.length<c;){if(1===h.nodeType){if(h.hasAttributes()){const t=[];for(const i of h.getAttributeNames())if(i.endsWith(o$2)||i.startsWith(n$3)){const s=f[d++];if(t.push(i),void 0!==s){const t=h.getAttribute(s.toLowerCase()+o$2).split(n$3),i=/([.?@])?(.*)/.exec(s);v.push({type:1,index:r,name:i[2],strings:t,ctor:"."===i[1]?H:"?"===i[1]?L:"@"===i[1]?z:k});}else v.push({type:6,index:r});}for(const i of t)h.removeAttribute(i);}if(y.test(h.tagName)){const t=h.textContent.split(n$3),i=t.length-1;if(i>0){h.textContent=s$1?s$1.emptyScript:"";for(let s=0;s<i;s++)h.append(t[s],u()),C.nextNode(),v.push({type:2,index:++r});h.append(t[i],u());}}}else if(8===h.nodeType)if(h.data===l$2)v.push({type:2,index:r});else {let t=-1;for(;-1!==(t=h.data.indexOf(n$3,t+1));)v.push({type:7,index:r}),t+=n$3.length-1;}r++;}}static createElement(t,i){const s=r.createElement("template");return s.innerHTML=t,s}}function S(t,i,s=t,e){var o,n,l,h;if(i===T)return i;let r=void 0!==e?null===(o=s._$Co)||void 0===o?void 0:o[e]:s._$Cl;const u=d(i)?void 0:i._$litDirective$;return (null==r?void 0:r.constructor)!==u&&(null===(n=null==r?void 0:r._$AO)||void 0===n||n.call(r,!1),void 0===u?r=void 0:(r=new u(t),r._$AT(t,s,e)),void 0!==e?(null!==(l=(h=s)._$Co)&&void 0!==l?l:h._$Co=[])[e]=r:s._$Cl=r),void 0!==r&&(i=S(t,r._$AS(t,i.values),r,e)),i}class M{constructor(t,i){this._$AV=[],this._$AN=void 0,this._$AD=t,this._$AM=i;}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(t){var i;const{el:{content:s},parts:e}=this._$AD,o=(null!==(i=null==t?void 0:t.creationScope)&&void 0!==i?i:r).importNode(s,!0);C.currentNode=o;let n=C.nextNode(),l=0,h=0,u=e[0];for(;void 0!==u;){if(l===u.index){let i;2===u.type?i=new R(n,n.nextSibling,this,t):1===u.type?i=new u.ctor(n,u.name,u.strings,this,t):6===u.type&&(i=new Z(n,this,t)),this._$AV.push(i),u=e[++h];}l!==(null==u?void 0:u.index)&&(n=C.nextNode(),l++);}return C.currentNode=r,o}v(t){let i=0;for(const s of this._$AV)void 0!==s&&(void 0!==s.strings?(s._$AI(t,s,i),i+=s.strings.length-2):s._$AI(t[i])),i++;}}class R{constructor(t,i,s,e){var o;this.type=2,this._$AH=A,this._$AN=void 0,this._$AA=t,this._$AB=i,this._$AM=s,this.options=e,this._$Cp=null===(o=null==e?void 0:e.isConnected)||void 0===o||o;}get _$AU(){var t,i;return null!==(i=null===(t=this._$AM)||void 0===t?void 0:t._$AU)&&void 0!==i?i:this._$Cp}get parentNode(){let t=this._$AA.parentNode;const i=this._$AM;return void 0!==i&&11===(null==t?void 0:t.nodeType)&&(t=i.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,i=this){t=S(this,t,i),d(t)?t===A||null==t||""===t?(this._$AH!==A&&this._$AR(),this._$AH=A):t!==this._$AH&&t!==T&&this._(t):void 0!==t._$litType$?this.g(t):void 0!==t.nodeType?this.$(t):v(t)?this.T(t):this._(t);}k(t){return this._$AA.parentNode.insertBefore(t,this._$AB)}$(t){this._$AH!==t&&(this._$AR(),this._$AH=this.k(t));}_(t){this._$AH!==A&&d(this._$AH)?this._$AA.nextSibling.data=t:this.$(r.createTextNode(t)),this._$AH=t;}g(t){var i;const{values:s,_$litType$:e}=t,o="number"==typeof e?this._$AC(t):(void 0===e.el&&(e.el=N.createElement(P(e.h,e.h[0]),this.options)),e);if((null===(i=this._$AH)||void 0===i?void 0:i._$AD)===o)this._$AH.v(s);else {const t=new M(o,this),i=t.u(this.options);t.v(s),this.$(i),this._$AH=t;}}_$AC(t){let i=E.get(t.strings);return void 0===i&&E.set(t.strings,i=new N(t)),i}T(t){c(this._$AH)||(this._$AH=[],this._$AR());const i=this._$AH;let s,e=0;for(const o of t)e===i.length?i.push(s=new R(this.k(u()),this.k(u()),this,this.options)):s=i[e],s._$AI(o),e++;e<i.length&&(this._$AR(s&&s._$AB.nextSibling,e),i.length=e);}_$AR(t=this._$AA.nextSibling,i){var s;for(null===(s=this._$AP)||void 0===s||s.call(this,!1,!0,i);t&&t!==this._$AB;){const i=t.nextSibling;t.remove(),t=i;}}setConnected(t){var i;void 0===this._$AM&&(this._$Cp=t,null===(i=this._$AP)||void 0===i||i.call(this,t));}}class k{constructor(t,i,s,e,o){this.type=1,this._$AH=A,this._$AN=void 0,this.element=t,this.name=i,this._$AM=e,this.options=o,s.length>2||""!==s[0]||""!==s[1]?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=A;}get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}_$AI(t,i=this,s,e){const o=this.strings;let n=!1;if(void 0===o)t=S(this,t,i,0),n=!d(t)||t!==this._$AH&&t!==T,n&&(this._$AH=t);else {const e=t;let l,h;for(t=o[0],l=0;l<o.length-1;l++)h=S(this,e[s+l],i,l),h===T&&(h=this._$AH[l]),n||(n=!d(h)||h!==this._$AH[l]),h===A?t=A:t!==A&&(t+=(null!=h?h:"")+o[l+1]),this._$AH[l]=h;}n&&!e&&this.j(t);}j(t){t===A?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,null!=t?t:"");}}class H extends k{constructor(){super(...arguments),this.type=3;}j(t){this.element[this.name]=t===A?void 0:t;}}const I=s$1?s$1.emptyScript:"";class L extends k{constructor(){super(...arguments),this.type=4;}j(t){t&&t!==A?this.element.setAttribute(this.name,I):this.element.removeAttribute(this.name);}}class z extends k{constructor(t,i,s,e,o){super(t,i,s,e,o),this.type=5;}_$AI(t,i=this){var s;if((t=null!==(s=S(this,t,i,0))&&void 0!==s?s:A)===T)return;const e=this._$AH,o=t===A&&e!==A||t.capture!==e.capture||t.once!==e.once||t.passive!==e.passive,n=t!==A&&(e===A||o);o&&this.element.removeEventListener(this.name,this,e),n&&this.element.addEventListener(this.name,this,t),this._$AH=t;}handleEvent(t){var i,s;"function"==typeof this._$AH?this._$AH.call(null!==(s=null===(i=this.options)||void 0===i?void 0:i.host)&&void 0!==s?s:this.element,t):this._$AH.handleEvent(t);}}class Z{constructor(t,i,s){this.element=t,this.type=6,this._$AN=void 0,this._$AM=i,this.options=s;}get _$AU(){return this._$AM._$AU}_$AI(t){S(this,t);}}const B=i$2.litHtmlPolyfillSupport;null==B||B(N,R),(null!==(t$2=i$2.litHtmlVersions)&&void 0!==t$2?t$2:i$2.litHtmlVersions=[]).push("2.8.0");const D=(t,i,s)=>{var e,o;const n=null!==(e=null==s?void 0:s.renderBefore)&&void 0!==e?e:i;let l=n._$litPart$;if(void 0===l){const t=null!==(o=null==s?void 0:s.renderBefore)&&void 0!==o?o:null;n._$litPart$=l=new R(i.insertBefore(u(),t),t,void 0,null!=s?s:{});}return l._$AI(t),l};

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var l$1,o$1;class s extends u$1{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0;}createRenderRoot(){var t,e;const i=super.createRenderRoot();return null!==(t=(e=this.renderOptions).renderBefore)&&void 0!==t||(e.renderBefore=i.firstChild),i}update(t){const i=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(t),this._$Do=D(i,this.renderRoot,this.renderOptions);}connectedCallback(){var t;super.connectedCallback(),null===(t=this._$Do)||void 0===t||t.setConnected(!0);}disconnectedCallback(){var t;super.disconnectedCallback(),null===(t=this._$Do)||void 0===t||t.setConnected(!1);}render(){return T}}s.finalized=!0,s._$litElement$=!0,null===(l$1=globalThis.litElementHydrateSupport)||void 0===l$1||l$1.call(globalThis,{LitElement:s});const n$2=globalThis.litElementPolyfillSupport;null==n$2||n$2({LitElement:s});(null!==(o$1=globalThis.litElementVersions)&&void 0!==o$1?o$1:globalThis.litElementVersions=[]).push("3.3.3");

/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */

function __decorate(decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
}

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const e$3=e=>n=>"function"==typeof n?((e,n)=>(customElements.define(e,n),n))(e,n):((e,n)=>{const{kind:t,elements:s}=n;return {kind:t,elements:s,finisher(n){customElements.define(e,n);}}})(e,n);

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const i$1=(i,e)=>"method"===e.kind&&e.descriptor&&!("value"in e.descriptor)?{...e,finisher(n){n.createProperty(e.key,i);}}:{kind:"field",key:Symbol(),placement:"own",descriptor:{},originalKey:e.key,initializer(){"function"==typeof e.initializer&&(this[e.key]=e.initializer.call(this));},finisher(n){n.createProperty(e.key,i);}},e$2=(i,e,n)=>{e.constructor.createProperty(n,i);};function n$1(n){return (t,o)=>void 0!==o?e$2(n,t,o):i$1(n,t)}

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function t$1(t){return n$1({...t,state:!0})}

/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var n;null!=(null===(n=window.HTMLSlotElement)||void 0===n?void 0:n.prototype.assignedElements)?(o,n)=>o.assignedElements(n):(o,n)=>o.assignedNodes(n).filter((o=>o.nodeType===Node.ELEMENT_NODE));

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const t={ATTRIBUTE:1,CHILD:2,PROPERTY:3,BOOLEAN_ATTRIBUTE:4,EVENT:5,ELEMENT:6},e$1=t=>(...e)=>({_$litDirective$:t,values:e});class i{constructor(t){}get _$AU(){return this._$AM._$AU}_$AT(t,e,i){this._$Ct=t,this._$AM=e,this._$Ci=i;}_$AS(t,e){return this.update(t,e)}update(t,e){return this.render(...e)}}

/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class e extends i{constructor(i){if(super(i),this.et=A,i.type!==t.CHILD)throw Error(this.constructor.directiveName+"() can only be used in child bindings")}render(r){if(r===A||null==r)return this.ft=void 0,this.et=r;if(r===T)return r;if("string"!=typeof r)throw Error(this.constructor.directiveName+"() called with a non-string value");if(r===this.et)return this.ft;this.et=r;const s=[r];return s.raw=s,this.ft={_$litType$:this.constructor.resultType,strings:s,values:[]}}}e.directiveName="unsafeHTML",e.resultType=1;const o=e$1(e);

/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const l=l=>null!=l?l:A;

// include: shell.js
// The Module object: Our interface to the outside world. We import
// and export values on it. There are various ways Module can be used:
// 1. Not defined. We create it here
// 2. A function parameter, function(moduleArg) => Promise<Module>
// 3. pre-run appended it, var Module = {}; ..generated code..
// 4. External script tag defines var Module.
// We need to check if Module already exists (e.g. case 3 above).
// Substitution will be replaced with actual code on later stage of the build,
// this way Closure Compiler will not mangle it (e.g. case 4. above).
// Note that if you want to run closure, and also to use Module
// after the generated code, you will need to define   var Module = {};
// before the code. Then that object will be used in the code, and you
// can continue to use Module afterwards as well.
var Module = typeof Module != "undefined" ? Module : {};

// Determine the runtime environment we are in. You can customize this by
// setting the ENVIRONMENT setting at compile time (see settings.js).
// Attempt to auto-detect the environment
var ENVIRONMENT_IS_WEB = typeof window == "object";

var ENVIRONMENT_IS_WORKER = typeof importScripts == "function";

// N.b. Electron.js environment is simultaneously a NODE-environment, but
// also a web environment.
var ENVIRONMENT_IS_NODE = typeof process == "object" && typeof process.versions == "object" && typeof process.versions.node == "string";

// --pre-jses are emitted after the Module integration code, so that they can
// refer to Module (if they choose; they can also define Module)
// include: /src/lib/binding_web/prefix.js
var TreeSitter = function() {
  var initPromise;
  var document = typeof window == "object" ? {
    currentScript: window.document.currentScript
  } : null;
  class Parser {
    constructor() {
      this.initialize();
    }
    initialize() {
      throw new Error("cannot construct a Parser before calling `init()`");
    }
    static init(moduleOptions) {
      if (initPromise) return initPromise;
      Module = Object.assign({}, Module, moduleOptions);
      return initPromise = new Promise(resolveInitPromise => {
        // end include: /src/lib/binding_web/prefix.js
        // Sometimes an existing Module object exists with properties
        // meant to overwrite the default module functionality. Here
        // we collect those properties and reapply _after_ we configure
        // the current environment's defaults to avoid having to be so
        // defensive during initialization.
        var moduleOverrides = Object.assign({}, Module);
        var arguments_ = [];
        var thisProgram = "./this.program";
        var quit_ = (status, toThrow) => {
          throw toThrow;
        };
        // `/` should be present at the end if `scriptDirectory` is not empty
        var scriptDirectory = "";
        function locateFile(path) {
          if (Module["locateFile"]) {
            return Module["locateFile"](path, scriptDirectory);
          }
          return scriptDirectory + path;
        }
        // Hooks that are implemented differently in different runtime environments.
        var readAsync, readBinary;
        if (ENVIRONMENT_IS_NODE) {
          // These modules will usually be used on Node.js. Load them eagerly to avoid
          // the complexity of lazy-loading.
          var fs = require("fs");
          var nodePath = require("path");
          scriptDirectory = __dirname + "/";
          // include: node_shell_read.js
          readBinary = filename => {
            // We need to re-wrap `file://` strings to URLs. Normalizing isn't
            // necessary in that case, the path should already be absolute.
            filename = isFileURI(filename) ? new URL(filename) : nodePath.normalize(filename);
            var ret = fs.readFileSync(filename);
            return ret;
          };
          readAsync = (filename, binary = true) => {
            // See the comment in the `readBinary` function.
            filename = isFileURI(filename) ? new URL(filename) : nodePath.normalize(filename);
            return new Promise((resolve, reject) => {
              fs.readFile(filename, binary ? undefined : "utf8", (err, data) => {
                if (err) reject(err); else resolve(binary ? data.buffer : data);
              });
            });
          };
          // end include: node_shell_read.js
          if (!Module["thisProgram"] && process.argv.length > 1) {
            thisProgram = process.argv[1].replace(/\\/g, "/");
          }
          arguments_ = process.argv.slice(2);
          if (typeof module != "undefined") {
            module["exports"] = Module;
          }
          quit_ = (status, toThrow) => {
            process.exitCode = status;
            throw toThrow;
          };
        } else // Note that this includes Node.js workers when relevant (pthreads is enabled).
        // Node.js workers are detected as a combination of ENVIRONMENT_IS_WORKER and
        // ENVIRONMENT_IS_NODE.
        if (ENVIRONMENT_IS_WEB || ENVIRONMENT_IS_WORKER) {
          if (ENVIRONMENT_IS_WORKER) {
            // Check worker, not web, since window could be polyfilled
            scriptDirectory = self.location.href;
          } else if (typeof document != "undefined" && document.currentScript) {
            // web
            scriptDirectory = document.currentScript.src;
          }
          // blob urls look like blob:http://site.com/etc/etc and we cannot infer anything from them.
          // otherwise, slice off the final part of the url to find the script directory.
          // if scriptDirectory does not contain a slash, lastIndexOf will return -1,
          // and scriptDirectory will correctly be replaced with an empty string.
          // If scriptDirectory contains a query (starting with ?) or a fragment (starting with #),
          // they are removed because they could contain a slash.
          if (scriptDirectory.startsWith("blob:")) {
            scriptDirectory = "";
          } else {
            scriptDirectory = scriptDirectory.substr(0, scriptDirectory.replace(/[?#].*/, "").lastIndexOf("/") + 1);
          }
          {
            // include: web_or_worker_shell_read.js
            if (ENVIRONMENT_IS_WORKER) {
              readBinary = url => {
                var xhr = new XMLHttpRequest;
                xhr.open("GET", url, false);
                xhr.responseType = "arraybuffer";
                xhr.send(null);
                return new Uint8Array(/** @type{!ArrayBuffer} */ (xhr.response));
              };
            }
            readAsync = url => {
              // Fetch has some additional restrictions over XHR, like it can't be used on a file:// url.
              // See https://github.com/github/fetch/pull/92#issuecomment-140665932
              // Cordova or Electron apps are typically loaded from a file:// url.
              // So use XHR on webview if URL is a file URL.
              if (isFileURI(url)) {
                return new Promise((reject, resolve) => {
                  var xhr = new XMLHttpRequest;
                  xhr.open("GET", url, true);
                  xhr.responseType = "arraybuffer";
                  xhr.onload = () => {
                    if (xhr.status == 200 || (xhr.status == 0 && xhr.response)) {
                      // file URLs can return 0
                      resolve(xhr.response);
                    }
                    reject(xhr.status);
                  };
                  xhr.onerror = reject;
                  xhr.send(null);
                });
              }
              return fetch(url, {
                credentials: "same-origin"
              }).then(response => {
                if (response.ok) {
                  return response.arrayBuffer();
                }
                return Promise.reject(new Error(response.status + " : " + response.url));
              });
            };
          }
        } else // end include: web_or_worker_shell_read.js
        ;
        var out = Module["print"] || console.log.bind(console);
        var err = Module["printErr"] || console.error.bind(console);
        // Merge back in the overrides
        Object.assign(Module, moduleOverrides);
        // Free the object hierarchy contained in the overrides, this lets the GC
        // reclaim data used.
        moduleOverrides = null;
        // Emit code to handle expected values on the Module object. This applies Module.x
        // to the proper local x. This has two benefits: first, we only emit it if it is
        // expected to arrive, and second, by using a local everywhere else that can be
        // minified.
        if (Module["arguments"]) arguments_ = Module["arguments"];
        if (Module["thisProgram"]) thisProgram = Module["thisProgram"];
        if (Module["quit"]) quit_ = Module["quit"];
        // perform assertions in shell.js after we set up out() and err(), as otherwise if an assertion fails it cannot print the message
        // end include: shell.js
        // include: preamble.js
        // === Preamble library stuff ===
        // Documentation for the public APIs defined in this file must be updated in:
        //    site/source/docs/api_reference/preamble.js.rst
        // A prebuilt local version of the documentation is available at:
        //    site/build/text/docs/api_reference/preamble.js.txt
        // You can also build docs locally as HTML or other formats in site/
        // An online HTML version (which may be of a different version of Emscripten)
        //    is up at http://kripken.github.io/emscripten-site/docs/api_reference/preamble.js.html
        var dynamicLibraries = Module["dynamicLibraries"] || [];
        var wasmBinary;
        if (Module["wasmBinary"]) wasmBinary = Module["wasmBinary"];
        // Wasm globals
        var wasmMemory;
        //========================================
        // Runtime essentials
        //========================================
        // whether we are quitting the application. no code should run after this.
        // set in exit() and abort()
        var ABORT = false;
        // set by exit() and abort().  Passed to 'onExit' handler.
        // NOTE: This is also used as the process return code code in shell environments
        // but only when noExitRuntime is false.
        var EXITSTATUS;
        // Memory management
        var /** @type {!Int8Array} */ HEAP8, /** @type {!Uint8Array} */ HEAPU8;
        var HEAP_DATA_VIEW;
        // include: runtime_shared.js
        function updateMemoryViews() {
          var b = wasmMemory.buffer;
          Module["HEAP_DATA_VIEW"] = HEAP_DATA_VIEW = new DataView(b);
          Module["HEAP8"] = HEAP8 = new Int8Array(b);
          Module["HEAP16"] = new Int16Array(b);
          Module["HEAPU8"] = HEAPU8 = new Uint8Array(b);
          Module["HEAPU16"] = new Uint16Array(b);
          Module["HEAP32"] = new Int32Array(b);
          Module["HEAPU32"] = new Uint32Array(b);
          Module["HEAPF32"] = new Float32Array(b);
          Module["HEAPF64"] = new Float64Array(b);
        }
        // end include: runtime_shared.js
        // In non-standalone/normal mode, we create the memory here.
        // include: runtime_init_memory.js
        // Create the wasm memory. (Note: this only applies if IMPORTED_MEMORY is defined)
        // check for full engine support (use string 'subarray' to avoid closure compiler confusion)
        if (Module["wasmMemory"]) {
          wasmMemory = Module["wasmMemory"];
        } else {
          var INITIAL_MEMORY = Module["INITIAL_MEMORY"] || 33554432;
          wasmMemory = new WebAssembly.Memory({
            "initial": INITIAL_MEMORY / 65536,
            // In theory we should not need to emit the maximum if we want "unlimited"
            // or 4GB of memory, but VMs error on that atm, see
            // https://github.com/emscripten-core/emscripten/issues/14130
            // And in the pthreads case we definitely need to emit a maximum. So
            // always emit one.
            "maximum": 2147483648 / 65536
          });
        }
        updateMemoryViews();
        // end include: runtime_init_memory.js
        // include: runtime_stack_check.js
        // end include: runtime_stack_check.js
        // include: runtime_assertions.js
        // end include: runtime_assertions.js
        var __ATPRERUN__ = [];
        // functions called before the runtime is initialized
        var __ATINIT__ = [];
        // functions called during startup
        var __ATMAIN__ = [];
        // functions called during shutdown
        var __ATPOSTRUN__ = [];
        // functions called after the main() is called
        var __RELOC_FUNCS__ = [];
        var runtimeInitialized = false;
        function preRun() {
          if (Module["preRun"]) {
            if (typeof Module["preRun"] == "function") Module["preRun"] = [ Module["preRun"] ];
            while (Module["preRun"].length) {
              addOnPreRun(Module["preRun"].shift());
            }
          }
          callRuntimeCallbacks(__ATPRERUN__);
        }
        function initRuntime() {
          runtimeInitialized = true;
          callRuntimeCallbacks(__RELOC_FUNCS__);
          callRuntimeCallbacks(__ATINIT__);
        }
        function preMain() {
          callRuntimeCallbacks(__ATMAIN__);
        }
        function postRun() {
          if (Module["postRun"]) {
            if (typeof Module["postRun"] == "function") Module["postRun"] = [ Module["postRun"] ];
            while (Module["postRun"].length) {
              addOnPostRun(Module["postRun"].shift());
            }
          }
          callRuntimeCallbacks(__ATPOSTRUN__);
        }
        function addOnPreRun(cb) {
          __ATPRERUN__.unshift(cb);
        }
        function addOnInit(cb) {
          __ATINIT__.unshift(cb);
        }
        function addOnPostRun(cb) {
          __ATPOSTRUN__.unshift(cb);
        }
        // include: runtime_math.js
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/imul
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/fround
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/clz32
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/trunc
        // end include: runtime_math.js
        // A counter of dependencies for calling run(). If we need to
        // do asynchronous work before running, increment this and
        // decrement it. Incrementing must happen in a place like
        // Module.preRun (used by emcc to add file preloading).
        // Note that you can add dependencies in preRun, even though
        // it happens right before run - run will be postponed until
        // the dependencies are met.
        var runDependencies = 0;
        var dependenciesFulfilled = null;
        // overridden to take different actions when all run dependencies are fulfilled
        function getUniqueRunDependency(id) {
          return id;
        }
        function addRunDependency(id) {
          runDependencies++;
          Module["monitorRunDependencies"]?.(runDependencies);
        }
        function removeRunDependency(id) {
          runDependencies--;
          Module["monitorRunDependencies"]?.(runDependencies);
          if (runDependencies == 0) {
            if (dependenciesFulfilled) {
              var callback = dependenciesFulfilled;
              dependenciesFulfilled = null;
              callback();
            }
          }
        }
        /** @param {string|number=} what */ function abort(what) {
          Module["onAbort"]?.(what);
          what = "Aborted(" + what + ")";
          // TODO(sbc): Should we remove printing and leave it up to whoever
          // catches the exception?
          err(what);
          ABORT = true;
          EXITSTATUS = 1;
          what += ". Build with -sASSERTIONS for more info.";
          // Use a wasm runtime error, because a JS error might be seen as a foreign
          // exception, which means we'd run destructors on it. We need the error to
          // simply make the program stop.
          // FIXME This approach does not work in Wasm EH because it currently does not assume
          // all RuntimeErrors are from traps; it decides whether a RuntimeError is from
          // a trap or not based on a hidden field within the object. So at the moment
          // we don't have a way of throwing a wasm trap from JS. TODO Make a JS API that
          // allows this in the wasm spec.
          // Suppress closure compiler warning here. Closure compiler's builtin extern
          // definition for WebAssembly.RuntimeError claims it takes no arguments even
          // though it can.
          // TODO(https://github.com/google/closure-compiler/pull/3913): Remove if/when upstream closure gets fixed.
          /** @suppress {checkTypes} */ var e = new WebAssembly.RuntimeError(what);
          // Throw the error whether or not MODULARIZE is set because abort is used
          // in code paths apart from instantiation where an exception is expected
          // to be thrown when abort is called.
          throw e;
        }
        // include: memoryprofiler.js
        // end include: memoryprofiler.js
        // include: URIUtils.js
        // Prefix of data URIs emitted by SINGLE_FILE and related options.
        var dataURIPrefix = "data:application/octet-stream;base64,";
        /**
 * Indicates whether filename is a base64 data URI.
 * @noinline
 */ var isDataURI = filename => filename.startsWith(dataURIPrefix);
        /**
 * Indicates whether filename is delivered via file protocol (as opposed to http/https)
 * @noinline
 */ var isFileURI = filename => filename.startsWith("file://");
        // end include: URIUtils.js
        // include: runtime_exceptions.js
        // end include: runtime_exceptions.js
        function findWasmBinary() {
          var f = "tree-sitter.wasm";
          if (!isDataURI(f)) {
            return locateFile(f);
          }
          return f;
        }
        var wasmBinaryFile;
        function getBinarySync(file) {
          if (file == wasmBinaryFile && wasmBinary) {
            return new Uint8Array(wasmBinary);
          }
          if (readBinary) {
            return readBinary(file);
          }
          throw "both async and sync fetching of the wasm failed";
        }
        function getBinaryPromise(binaryFile) {
          // If we don't have the binary yet, load it asynchronously using readAsync.
          if (!wasmBinary) {
            // Fetch the binary using readAsync
            return readAsync(binaryFile).then(response => new Uint8Array(/** @type{!ArrayBuffer} */ (response)), // Fall back to getBinarySync if readAsync fails
            () => getBinarySync(binaryFile));
          }
          // Otherwise, getBinarySync should be able to get it synchronously
          return Promise.resolve().then(() => getBinarySync(binaryFile));
        }
        function instantiateArrayBuffer(binaryFile, imports, receiver) {
          return getBinaryPromise(binaryFile).then(binary => WebAssembly.instantiate(binary, imports)).then(receiver, reason => {
            err(`failed to asynchronously prepare wasm: ${reason}`);
            abort(reason);
          });
        }
        function instantiateAsync(binary, binaryFile, imports, callback) {
          if (!binary && typeof WebAssembly.instantiateStreaming == "function" && !isDataURI(binaryFile) && // Don't use streaming for file:// delivered objects in a webview, fetch them synchronously.
          !isFileURI(binaryFile) && // Avoid instantiateStreaming() on Node.js environment for now, as while
          // Node.js v18.1.0 implements it, it does not have a full fetch()
          // implementation yet.
          // Reference:
          //   https://github.com/emscripten-core/emscripten/pull/16917
          !ENVIRONMENT_IS_NODE && typeof fetch == "function") {
            return fetch(binaryFile, {
              credentials: "same-origin"
            }).then(response => {
              // Suppress closure warning here since the upstream definition for
              // instantiateStreaming only allows Promise<Repsponse> rather than
              // an actual Response.
              // TODO(https://github.com/google/closure-compiler/pull/3913): Remove if/when upstream closure is fixed.
              /** @suppress {checkTypes} */ var result = WebAssembly.instantiateStreaming(response, imports);
              return result.then(callback, function(reason) {
                // We expect the most common failure cause to be a bad MIME type for the binary,
                // in which case falling back to ArrayBuffer instantiation should work.
                err(`wasm streaming compile failed: ${reason}`);
                err("falling back to ArrayBuffer instantiation");
                return instantiateArrayBuffer(binaryFile, imports, callback);
              });
            });
          }
          return instantiateArrayBuffer(binaryFile, imports, callback);
        }
        function getWasmImports() {
          // prepare imports
          return {
            "env": wasmImports,
            "wasi_snapshot_preview1": wasmImports,
            "GOT.mem": new Proxy(wasmImports, GOTHandler),
            "GOT.func": new Proxy(wasmImports, GOTHandler)
          };
        }
        // Create the wasm instance.
        // Receives the wasm imports, returns the exports.
        function createWasm() {
          var info = getWasmImports();
          // Load the wasm module and create an instance of using native support in the JS engine.
          // handle a generated wasm instance, receiving its exports and
          // performing other necessary setup
          /** @param {WebAssembly.Module=} module*/ function receiveInstance(instance, module) {
            wasmExports = instance.exports;
            wasmExports = relocateExports(wasmExports, 1024);
            var metadata = getDylinkMetadata(module);
            if (metadata.neededDynlibs) {
              dynamicLibraries = metadata.neededDynlibs.concat(dynamicLibraries);
            }
            mergeLibSymbols(wasmExports);
            LDSO.init();
            loadDylibs();
            addOnInit(wasmExports["__wasm_call_ctors"]);
            __RELOC_FUNCS__.push(wasmExports["__wasm_apply_data_relocs"]);
            removeRunDependency();
            return wasmExports;
          }
          // wait for the pthread pool (if any)
          addRunDependency();
          // Prefer streaming instantiation if available.
          function receiveInstantiationResult(result) {
            // 'result' is a ResultObject object which has both the module and instance.
            // receiveInstance() will swap in the exports (to Module.asm) so they can be called
            receiveInstance(result["instance"], result["module"]);
          }
          // User shell pages can write their own Module.instantiateWasm = function(imports, successCallback) callback
          // to manually instantiate the Wasm module themselves. This allows pages to
          // run the instantiation parallel to any other async startup actions they are
          // performing.
          // Also pthreads and wasm workers initialize the wasm instance through this
          // path.
          if (Module["instantiateWasm"]) {
            try {
              return Module["instantiateWasm"](info, receiveInstance);
            } catch (e) {
              err(`Module.instantiateWasm callback failed with error: ${e}`);
              return false;
            }
          }
          if (!wasmBinaryFile) wasmBinaryFile = findWasmBinary();
          instantiateAsync(wasmBinary, wasmBinaryFile, info, receiveInstantiationResult);
          return {};
        }
        // end include: preamble.js
        /** @constructor */ function ExitStatus(status) {
          this.name = "ExitStatus";
          this.message = `Program terminated with exit(${status})`;
          this.status = status;
        }
        var GOT = {};
        var currentModuleWeakSymbols = new Set([]);
        var GOTHandler = {
          get(obj, symName) {
            var rtn = GOT[symName];
            if (!rtn) {
              rtn = GOT[symName] = new WebAssembly.Global({
                "value": "i32",
                "mutable": true
              });
            }
            if (!currentModuleWeakSymbols.has(symName)) {
              // Any non-weak reference to a symbol marks it as `required`, which
              // enabled `reportUndefinedSymbols` to report undefeind symbol errors
              // correctly.
              rtn.required = true;
            }
            return rtn;
          }
        };
        var LE_HEAP_LOAD_F32 = byteOffset => HEAP_DATA_VIEW.getFloat32(byteOffset, true);
        var LE_HEAP_LOAD_F64 = byteOffset => HEAP_DATA_VIEW.getFloat64(byteOffset, true);
        var LE_HEAP_LOAD_I16 = byteOffset => HEAP_DATA_VIEW.getInt16(byteOffset, true);
        var LE_HEAP_LOAD_I32 = byteOffset => HEAP_DATA_VIEW.getInt32(byteOffset, true);
        var LE_HEAP_LOAD_U32 = byteOffset => HEAP_DATA_VIEW.getUint32(byteOffset, true);
        var LE_HEAP_STORE_F32 = (byteOffset, value) => HEAP_DATA_VIEW.setFloat32(byteOffset, value, true);
        var LE_HEAP_STORE_F64 = (byteOffset, value) => HEAP_DATA_VIEW.setFloat64(byteOffset, value, true);
        var LE_HEAP_STORE_I16 = (byteOffset, value) => HEAP_DATA_VIEW.setInt16(byteOffset, value, true);
        var LE_HEAP_STORE_I32 = (byteOffset, value) => HEAP_DATA_VIEW.setInt32(byteOffset, value, true);
        var LE_HEAP_STORE_U32 = (byteOffset, value) => HEAP_DATA_VIEW.setUint32(byteOffset, value, true);
        var callRuntimeCallbacks = callbacks => {
          while (callbacks.length > 0) {
            // Pass the module as the first argument.
            callbacks.shift()(Module);
          }
        };
        var UTF8Decoder = typeof TextDecoder != "undefined" ? new TextDecoder : undefined;
        /**
     * Given a pointer 'idx' to a null-terminated UTF8-encoded string in the given
     * array that contains uint8 values, returns a copy of that string as a
     * Javascript String object.
     * heapOrArray is either a regular array, or a JavaScript typed array view.
     * @param {number} idx
     * @param {number=} maxBytesToRead
     * @return {string}
     */ var UTF8ArrayToString = (heapOrArray, idx, maxBytesToRead) => {
          var endIdx = idx + maxBytesToRead;
          var endPtr = idx;
          // TextDecoder needs to know the byte length in advance, it doesn't stop on
          // null terminator by itself.  Also, use the length info to avoid running tiny
          // strings through TextDecoder, since .subarray() allocates garbage.
          // (As a tiny code save trick, compare endPtr against endIdx using a negation,
          // so that undefined means Infinity)
          while (heapOrArray[endPtr] && !(endPtr >= endIdx)) ++endPtr;
          if (endPtr - idx > 16 && heapOrArray.buffer && UTF8Decoder) {
            return UTF8Decoder.decode(heapOrArray.subarray(idx, endPtr));
          }
          var str = "";
          // If building with TextDecoder, we have already computed the string length
          // above, so test loop end condition against that
          while (idx < endPtr) {
            // For UTF8 byte structure, see:
            // http://en.wikipedia.org/wiki/UTF-8#Description
            // https://www.ietf.org/rfc/rfc2279.txt
            // https://tools.ietf.org/html/rfc3629
            var u0 = heapOrArray[idx++];
            if (!(u0 & 128)) {
              str += String.fromCharCode(u0);
              continue;
            }
            var u1 = heapOrArray[idx++] & 63;
            if ((u0 & 224) == 192) {
              str += String.fromCharCode(((u0 & 31) << 6) | u1);
              continue;
            }
            var u2 = heapOrArray[idx++] & 63;
            if ((u0 & 240) == 224) {
              u0 = ((u0 & 15) << 12) | (u1 << 6) | u2;
            } else {
              u0 = ((u0 & 7) << 18) | (u1 << 12) | (u2 << 6) | (heapOrArray[idx++] & 63);
            }
            if (u0 < 65536) {
              str += String.fromCharCode(u0);
            } else {
              var ch = u0 - 65536;
              str += String.fromCharCode(55296 | (ch >> 10), 56320 | (ch & 1023));
            }
          }
          return str;
        };
        var getDylinkMetadata = binary => {
          var offset = 0;
          var end = 0;
          function getU8() {
            return binary[offset++];
          }
          function getLEB() {
            var ret = 0;
            var mul = 1;
            while (1) {
              var byte = binary[offset++];
              ret += ((byte & 127) * mul);
              mul *= 128;
              if (!(byte & 128)) break;
            }
            return ret;
          }
          function getString() {
            var len = getLEB();
            offset += len;
            return UTF8ArrayToString(binary, offset - len, len);
          }
          /** @param {string=} message */ function failIf(condition, message) {
            if (condition) throw new Error(message);
          }
          var name = "dylink.0";
          if (binary instanceof WebAssembly.Module) {
            var dylinkSection = WebAssembly.Module.customSections(binary, name);
            if (dylinkSection.length === 0) {
              name = "dylink";
              dylinkSection = WebAssembly.Module.customSections(binary, name);
            }
            failIf(dylinkSection.length === 0, "need dylink section");
            binary = new Uint8Array(dylinkSection[0]);
            end = binary.length;
          } else {
            var int32View = new Uint32Array(new Uint8Array(binary.subarray(0, 24)).buffer);
            var magicNumberFound = int32View[0] == 1836278016 || int32View[0] == 6386541;
            failIf(!magicNumberFound, "need to see wasm magic number");
            // \0asm
            // we should see the dylink custom section right after the magic number and wasm version
            failIf(binary[8] !== 0, "need the dylink section to be first");
            offset = 9;
            var section_size = getLEB();
            //section size
            end = offset + section_size;
            name = getString();
          }
          var customSection = {
            neededDynlibs: [],
            tlsExports: new Set,
            weakImports: new Set
          };
          if (name == "dylink") {
            customSection.memorySize = getLEB();
            customSection.memoryAlign = getLEB();
            customSection.tableSize = getLEB();
            customSection.tableAlign = getLEB();
            // shared libraries this module needs. We need to load them first, so that
            // current module could resolve its imports. (see tools/shared.py
            // WebAssembly.make_shared_library() for "dylink" section extension format)
            var neededDynlibsCount = getLEB();
            for (var i = 0; i < neededDynlibsCount; ++i) {
              var libname = getString();
              customSection.neededDynlibs.push(libname);
            }
          } else {
            failIf(name !== "dylink.0");
            var WASM_DYLINK_MEM_INFO = 1;
            var WASM_DYLINK_NEEDED = 2;
            var WASM_DYLINK_EXPORT_INFO = 3;
            var WASM_DYLINK_IMPORT_INFO = 4;
            var WASM_SYMBOL_TLS = 256;
            var WASM_SYMBOL_BINDING_MASK = 3;
            var WASM_SYMBOL_BINDING_WEAK = 1;
            while (offset < end) {
              var subsectionType = getU8();
              var subsectionSize = getLEB();
              if (subsectionType === WASM_DYLINK_MEM_INFO) {
                customSection.memorySize = getLEB();
                customSection.memoryAlign = getLEB();
                customSection.tableSize = getLEB();
                customSection.tableAlign = getLEB();
              } else if (subsectionType === WASM_DYLINK_NEEDED) {
                var neededDynlibsCount = getLEB();
                for (var i = 0; i < neededDynlibsCount; ++i) {
                  libname = getString();
                  customSection.neededDynlibs.push(libname);
                }
              } else if (subsectionType === WASM_DYLINK_EXPORT_INFO) {
                var count = getLEB();
                while (count--) {
                  var symname = getString();
                  var flags = getLEB();
                  if (flags & WASM_SYMBOL_TLS) {
                    customSection.tlsExports.add(symname);
                  }
                }
              } else if (subsectionType === WASM_DYLINK_IMPORT_INFO) {
                var count = getLEB();
                while (count--) {
                  getString();
                  var symname = getString();
                  var flags = getLEB();
                  if ((flags & WASM_SYMBOL_BINDING_MASK) == WASM_SYMBOL_BINDING_WEAK) {
                    customSection.weakImports.add(symname);
                  }
                }
              } else {
                // unknown subsection
                offset += subsectionSize;
              }
            }
          }
          return customSection;
        };
        /**
     * @param {number} ptr
     * @param {string} type
     */ function getValue(ptr, type = "i8") {
          if (type.endsWith("*")) type = "*";
          switch (type) {
           case "i1":
            return HEAP8[ptr];

           case "i8":
            return HEAP8[ptr];

           case "i16":
            return LE_HEAP_LOAD_I16(((ptr) >> 1) * 2);

           case "i32":
            return LE_HEAP_LOAD_I32(((ptr) >> 2) * 4);

           case "i64":
            abort("to do getValue(i64) use WASM_BIGINT");

           case "float":
            return LE_HEAP_LOAD_F32(((ptr) >> 2) * 4);

           case "double":
            return LE_HEAP_LOAD_F64(((ptr) >> 3) * 8);

           case "*":
            return LE_HEAP_LOAD_U32(((ptr) >> 2) * 4);

           default:
            abort(`invalid type for getValue: ${type}`);
          }
        }
        var newDSO = (name, handle, syms) => {
          var dso = {
            refcount: Infinity,
            name: name,
            exports: syms,
            global: true
          };
          LDSO.loadedLibsByName[name] = dso;
          if (handle != undefined) {
            LDSO.loadedLibsByHandle[handle] = dso;
          }
          return dso;
        };
        var LDSO = {
          loadedLibsByName: {},
          loadedLibsByHandle: {},
          init() {
            newDSO("__main__", 0, wasmImports);
          }
        };
        var ___heap_base = 78112;
        var zeroMemory = (address, size) => {
          HEAPU8.fill(0, address, address + size);
          return address;
        };
        var alignMemory = (size, alignment) => Math.ceil(size / alignment) * alignment;
        var getMemory = size => {
          // After the runtime is initialized, we must only use sbrk() normally.
          if (runtimeInitialized) {
            // Currently we don't support freeing of static data when modules are
            // unloaded via dlclose.  This function is tagged as `noleakcheck` to
            // avoid having this reported as leak.
            return zeroMemory(_malloc(size), size);
          }
          var ret = ___heap_base;
          // Keep __heap_base stack aligned.
          var end = ret + alignMemory(size, 16);
          ___heap_base = end;
          GOT["__heap_base"].value = end;
          return ret;
        };
        var isInternalSym = symName => [ "__cpp_exception", "__c_longjmp", "__wasm_apply_data_relocs", "__dso_handle", "__tls_size", "__tls_align", "__set_stack_limits", "_emscripten_tls_init", "__wasm_init_tls", "__wasm_call_ctors", "__start_em_asm", "__stop_em_asm", "__start_em_js", "__stop_em_js" ].includes(symName) || symName.startsWith("__em_js__");
        var uleb128Encode = (n, target) => {
          if (n < 128) {
            target.push(n);
          } else {
            target.push((n % 128) | 128, n >> 7);
          }
        };
        var sigToWasmTypes = sig => {
          var typeNames = {
            "i": "i32",
            "j": "i64",
            "f": "f32",
            "d": "f64",
            "e": "externref",
            "p": "i32"
          };
          var type = {
            parameters: [],
            results: sig[0] == "v" ? [] : [ typeNames[sig[0]] ]
          };
          for (var i = 1; i < sig.length; ++i) {
            type.parameters.push(typeNames[sig[i]]);
          }
          return type;
        };
        var generateFuncType = (sig, target) => {
          var sigRet = sig.slice(0, 1);
          var sigParam = sig.slice(1);
          var typeCodes = {
            "i": 127,
            // i32
            "p": 127,
            // i32
            "j": 126,
            // i64
            "f": 125,
            // f32
            "d": 124,
            // f64
            "e": 111
          };
          // Parameters, length + signatures
          target.push(96);
          /* form: func */ uleb128Encode(sigParam.length, target);
          for (var i = 0; i < sigParam.length; ++i) {
            target.push(typeCodes[sigParam[i]]);
          }
          // Return values, length + signatures
          // With no multi-return in MVP, either 0 (void) or 1 (anything else)
          if (sigRet == "v") {
            target.push(0);
          } else {
            target.push(1, typeCodes[sigRet]);
          }
        };
        var convertJsFunctionToWasm = (func, sig) => {
          // If the type reflection proposal is available, use the new
          // "WebAssembly.Function" constructor.
          // Otherwise, construct a minimal wasm module importing the JS function and
          // re-exporting it.
          if (typeof WebAssembly.Function == "function") {
            return new WebAssembly.Function(sigToWasmTypes(sig), func);
          }
          // The module is static, with the exception of the type section, which is
          // generated based on the signature passed in.
          var typeSectionBody = [ 1 ];
          // count: 1
          generateFuncType(sig, typeSectionBody);
          // Rest of the module is static
          var bytes = [ 0, 97, 115, 109, // magic ("\0asm")
          1, 0, 0, 0, // version: 1
          1 ];
          // Write the overall length of the type section followed by the body
          uleb128Encode(typeSectionBody.length, bytes);
          bytes.push(...typeSectionBody);
          // The rest of the module is static
          bytes.push(2, 7, // import section
          // (import "e" "f" (func 0 (type 0)))
          1, 1, 101, 1, 102, 0, 0, 7, 5, // export section
          // (export "f" (func 0 (type 0)))
          1, 1, 102, 0, 0);
          // We can compile this wasm module synchronously because it is very small.
          // This accepts an import (at "e.f"), that it reroutes to an export (at "f")
          var module = new WebAssembly.Module(new Uint8Array(bytes));
          var instance = new WebAssembly.Instance(module, {
            "e": {
              "f": func
            }
          });
          var wrappedFunc = instance.exports["f"];
          return wrappedFunc;
        };
        var wasmTableMirror = [];
        /** @type {WebAssembly.Table} */ var wasmTable = new WebAssembly.Table({
          "initial": 28,
          "element": "anyfunc"
        });
        var getWasmTableEntry = funcPtr => {
          var func = wasmTableMirror[funcPtr];
          if (!func) {
            if (funcPtr >= wasmTableMirror.length) wasmTableMirror.length = funcPtr + 1;
            wasmTableMirror[funcPtr] = func = wasmTable.get(funcPtr);
          }
          return func;
        };
        var updateTableMap = (offset, count) => {
          if (functionsInTableMap) {
            for (var i = offset; i < offset + count; i++) {
              var item = getWasmTableEntry(i);
              // Ignore null values.
              if (item) {
                functionsInTableMap.set(item, i);
              }
            }
          }
        };
        var functionsInTableMap;
        var getFunctionAddress = func => {
          // First, create the map if this is the first use.
          if (!functionsInTableMap) {
            functionsInTableMap = new WeakMap;
            updateTableMap(0, wasmTable.length);
          }
          return functionsInTableMap.get(func) || 0;
        };
        var freeTableIndexes = [];
        var getEmptyTableSlot = () => {
          // Reuse a free index if there is one, otherwise grow.
          if (freeTableIndexes.length) {
            return freeTableIndexes.pop();
          }
          // Grow the table
          try {
            wasmTable.grow(1);
          } catch (err) {
            if (!(err instanceof RangeError)) {
              throw err;
            }
            throw "Unable to grow wasm table. Set ALLOW_TABLE_GROWTH.";
          }
          return wasmTable.length - 1;
        };
        var setWasmTableEntry = (idx, func) => {
          wasmTable.set(idx, func);
          // With ABORT_ON_WASM_EXCEPTIONS wasmTable.get is overridden to return wrapped
          // functions so we need to call it here to retrieve the potential wrapper correctly
          // instead of just storing 'func' directly into wasmTableMirror
          wasmTableMirror[idx] = wasmTable.get(idx);
        };
        /** @param {string=} sig */ var addFunction = (func, sig) => {
          // Check if the function is already in the table, to ensure each function
          // gets a unique index.
          var rtn = getFunctionAddress(func);
          if (rtn) {
            return rtn;
          }
          // It's not in the table, add it now.
          var ret = getEmptyTableSlot();
          // Set the new value.
          try {
            // Attempting to call this with JS function will cause of table.set() to fail
            setWasmTableEntry(ret, func);
          } catch (err) {
            if (!(err instanceof TypeError)) {
              throw err;
            }
            var wrapped = convertJsFunctionToWasm(func, sig);
            setWasmTableEntry(ret, wrapped);
          }
          functionsInTableMap.set(func, ret);
          return ret;
        };
        var updateGOT = (exports, replace) => {
          for (var symName in exports) {
            if (isInternalSym(symName)) {
              continue;
            }
            var value = exports[symName];
            if (symName.startsWith("orig$")) {
              symName = symName.split("$")[1];
              replace = true;
            }
            GOT[symName] ||= new WebAssembly.Global({
              "value": "i32",
              "mutable": true
            });
            if (replace || GOT[symName].value == 0) {
              if (typeof value == "function") {
                GOT[symName].value = addFunction(value);
              } else if (typeof value == "number") {
                GOT[symName].value = value;
              } else {
                err(`unhandled export type for '${symName}': ${typeof value}`);
              }
            }
          }
        };
        /** @param {boolean=} replace */ var relocateExports = (exports, memoryBase, replace) => {
          var relocated = {};
          for (var e in exports) {
            var value = exports[e];
            if (typeof value == "object") {
              // a breaking change in the wasm spec, globals are now objects
              // https://github.com/WebAssembly/mutable-global/issues/1
              value = value.value;
            }
            if (typeof value == "number") {
              value += memoryBase;
            }
            relocated[e] = value;
          }
          updateGOT(relocated, replace);
          return relocated;
        };
        var isSymbolDefined = symName => {
          // Ignore 'stub' symbols that are auto-generated as part of the original
          // `wasmImports` used to instantiate the main module.
          var existing = wasmImports[symName];
          if (!existing || existing.stub) {
            return false;
          }
          return true;
        };
        var dynCallLegacy = (sig, ptr, args) => {
          sig = sig.replace(/p/g, "i");
          var f = Module["dynCall_" + sig];
          return f(ptr, ...args);
        };
        var dynCall = (sig, ptr, args = []) => {
          // Without WASM_BIGINT support we cannot directly call function with i64 as
          // part of their signature, so we rely on the dynCall functions generated by
          // wasm-emscripten-finalize
          if (sig.includes("j")) {
            return dynCallLegacy(sig, ptr, args);
          }
          var rtn = getWasmTableEntry(ptr)(...args);
          return rtn;
        };
        var stackSave = () => _emscripten_stack_get_current();
        var stackRestore = val => __emscripten_stack_restore(val);
        var createInvokeFunction = sig => (ptr, ...args) => {
          var sp = stackSave();
          try {
            return dynCall(sig, ptr, args);
          } catch (e) {
            stackRestore(sp);
            // Create a try-catch guard that rethrows the Emscripten EH exception.
            // Exceptions thrown from C++ will be a pointer (number) and longjmp
            // will throw the number Infinity. Use the compact and fast "e !== e+0"
            // test to check if e was not a Number.
            if (e !== e + 0) throw e;
            _setThrew(1, 0);
          }
        };
        var resolveGlobalSymbol = (symName, direct = false) => {
          var sym;
          // First look for the orig$ symbol which is the symbol without i64
          // legalization performed.
          if (direct && ("orig$" + symName in wasmImports)) {
            symName = "orig$" + symName;
          }
          if (isSymbolDefined(symName)) {
            sym = wasmImports[symName];
          } else // Asm.js-style exception handling: invoke wrapper generation
          if (symName.startsWith("invoke_")) {
            // Create (and cache) new invoke_ functions on demand.
            sym = wasmImports[symName] = createInvokeFunction(symName.split("_")[1]);
          }
          return {
            sym: sym,
            name: symName
          };
        };
        /**
     * Given a pointer 'ptr' to a null-terminated UTF8-encoded string in the
     * emscripten HEAP, returns a copy of that string as a Javascript String object.
     *
     * @param {number} ptr
     * @param {number=} maxBytesToRead - An optional length that specifies the
     *   maximum number of bytes to read. You can omit this parameter to scan the
     *   string until the first 0 byte. If maxBytesToRead is passed, and the string
     *   at [ptr, ptr+maxBytesToReadr[ contains a null byte in the middle, then the
     *   string will cut short at that byte index (i.e. maxBytesToRead will not
     *   produce a string of exact length [ptr, ptr+maxBytesToRead[) N.B. mixing
     *   frequent uses of UTF8ToString() with and without maxBytesToRead may throw
     *   JS JIT optimizations off, so it is worth to consider consistently using one
     * @return {string}
     */ var UTF8ToString = (ptr, maxBytesToRead) => ptr ? UTF8ArrayToString(HEAPU8, ptr, maxBytesToRead) : "";
        /**
      * @param {string=} libName
      * @param {Object=} localScope
      * @param {number=} handle
      */ var loadWebAssemblyModule = (binary, flags, libName, localScope, handle) => {
          var metadata = getDylinkMetadata(binary);
          currentModuleWeakSymbols = metadata.weakImports;
          // loadModule loads the wasm module after all its dependencies have been loaded.
          // can be called both sync/async.
          function loadModule() {
            // The first thread to load a given module needs to allocate the static
            // table and memory regions.  Later threads re-use the same table region
            // and can ignore the memory region (since memory is shared between
            // threads already).
            // If `handle` is specified than it is assumed that the calling thread has
            // exclusive access to it for the duration of this function.  See the
            // locking in `dynlink.c`.
            var firstLoad = !handle || !HEAP8[(handle) + (8)];
            if (firstLoad) {
              // alignments are powers of 2
              var memAlign = Math.pow(2, metadata.memoryAlign);
              // prepare memory
              var memoryBase = metadata.memorySize ? alignMemory(getMemory(metadata.memorySize + memAlign), memAlign) : 0;
              // TODO: add to cleanups
              var tableBase = metadata.tableSize ? wasmTable.length : 0;
              if (handle) {
                HEAP8[(handle) + (8)] = 1;
                LE_HEAP_STORE_U32((((handle) + (12)) >> 2) * 4, memoryBase);
                LE_HEAP_STORE_I32((((handle) + (16)) >> 2) * 4, metadata.memorySize);
                LE_HEAP_STORE_U32((((handle) + (20)) >> 2) * 4, tableBase);
                LE_HEAP_STORE_I32((((handle) + (24)) >> 2) * 4, metadata.tableSize);
              }
            } else {
              memoryBase = LE_HEAP_LOAD_U32((((handle) + (12)) >> 2) * 4);
              tableBase = LE_HEAP_LOAD_U32((((handle) + (20)) >> 2) * 4);
            }
            var tableGrowthNeeded = tableBase + metadata.tableSize - wasmTable.length;
            if (tableGrowthNeeded > 0) {
              wasmTable.grow(tableGrowthNeeded);
            }
            // This is the export map that we ultimately return.  We declare it here
            // so it can be used within resolveSymbol.  We resolve symbols against
            // this local symbol map in the case there they are not present on the
            // global Module object.  We need this fallback because Modules sometime
            // need to import their own symbols
            var moduleExports;
            function resolveSymbol(sym) {
              var resolved = resolveGlobalSymbol(sym).sym;
              if (!resolved && localScope) {
                resolved = localScope[sym];
              }
              if (!resolved) {
                resolved = moduleExports[sym];
              }
              return resolved;
            }
            // TODO kill ↓↓↓ (except "symbols local to this module", it will likely be
            // not needed if we require that if A wants symbols from B it has to link
            // to B explicitly: similarly to -Wl,--no-undefined)
            // wasm dynamic libraries are pure wasm, so they cannot assist in
            // their own loading. When side module A wants to import something
            // provided by a side module B that is loaded later, we need to
            // add a layer of indirection, but worse, we can't even tell what
            // to add the indirection for, without inspecting what A's imports
            // are. To do that here, we use a JS proxy (another option would
            // be to inspect the binary directly).
            var proxyHandler = {
              get(stubs, prop) {
                // symbols that should be local to this module
                switch (prop) {
                 case "__memory_base":
                  return memoryBase;

                 case "__table_base":
                  return tableBase;
                }
                if (prop in wasmImports && !wasmImports[prop].stub) {
                  // No stub needed, symbol already exists in symbol table
                  return wasmImports[prop];
                }
                // Return a stub function that will resolve the symbol
                // when first called.
                if (!(prop in stubs)) {
                  var resolved;
                  stubs[prop] = (...args) => {
                    resolved ||= resolveSymbol(prop);
                    return resolved(...args);
                  };
                }
                return stubs[prop];
              }
            };
            var proxy = new Proxy({}, proxyHandler);
            var info = {
              "GOT.mem": new Proxy({}, GOTHandler),
              "GOT.func": new Proxy({}, GOTHandler),
              "env": proxy,
              "wasi_snapshot_preview1": proxy
            };
            function postInstantiation(module, instance) {
              // add new entries to functionsInTableMap
              updateTableMap(tableBase, metadata.tableSize);
              moduleExports = relocateExports(instance.exports, memoryBase);
              if (!flags.allowUndefined) {
                reportUndefinedSymbols();
              }
              function addEmAsm(addr, body) {
                var args = [];
                var arity = 0;
                for (;arity < 16; arity++) {
                  if (body.indexOf("$" + arity) != -1) {
                    args.push("$" + arity);
                  } else {
                    break;
                  }
                }
                args = args.join(",");
                var func = `(${args}) => { ${body} };`;
                eval(func);
              }
              // Add any EM_ASM function that exist in the side module
              if ("__start_em_asm" in moduleExports) {
                var start = moduleExports["__start_em_asm"];
                var stop = moduleExports["__stop_em_asm"];
                while (start < stop) {
                  var jsString = UTF8ToString(start);
                  addEmAsm(start, jsString);
                  start = HEAPU8.indexOf(0, start) + 1;
                }
              }
              function addEmJs(name, cSig, body) {
                // The signature here is a C signature (e.g. "(int foo, char* bar)").
                // See `create_em_js` in emcc.py` for the build-time version of this
                // code.
                var jsArgs = [];
                cSig = cSig.slice(1, -1);
                if (cSig != "void") {
                  cSig = cSig.split(",");
                  for (var i in cSig) {
                    var jsArg = cSig[i].split(" ").pop();
                    jsArgs.push(jsArg.replace("*", ""));
                  }
                }
                var func = `(${jsArgs}) => ${body};`;
                moduleExports[name] = eval(func);
              }
              for (var name in moduleExports) {
                if (name.startsWith("__em_js__")) {
                  var start = moduleExports[name];
                  var jsString = UTF8ToString(start);
                  // EM_JS strings are stored in the data section in the form
                  // SIG<::>BODY.
                  var parts = jsString.split("<::>");
                  addEmJs(name.replace("__em_js__", ""), parts[0], parts[1]);
                  delete moduleExports[name];
                }
              }
              // initialize the module
              var applyRelocs = moduleExports["__wasm_apply_data_relocs"];
              if (applyRelocs) {
                if (runtimeInitialized) {
                  applyRelocs();
                } else {
                  __RELOC_FUNCS__.push(applyRelocs);
                }
              }
              var init = moduleExports["__wasm_call_ctors"];
              if (init) {
                if (runtimeInitialized) {
                  init();
                } else {
                  // we aren't ready to run compiled code yet
                  __ATINIT__.push(init);
                }
              }
              return moduleExports;
            }
            if (flags.loadAsync) {
              if (binary instanceof WebAssembly.Module) {
                var instance = new WebAssembly.Instance(binary, info);
                return Promise.resolve(postInstantiation(binary, instance));
              }
              return WebAssembly.instantiate(binary, info).then(result => postInstantiation(result.module, result.instance));
            }
            var module = binary instanceof WebAssembly.Module ? binary : new WebAssembly.Module(binary);
            var instance = new WebAssembly.Instance(module, info);
            return postInstantiation(module, instance);
          }
          // now load needed libraries and the module itself.
          if (flags.loadAsync) {
            return metadata.neededDynlibs.reduce((chain, dynNeeded) => chain.then(() => loadDynamicLibrary(dynNeeded, flags, localScope)), Promise.resolve()).then(loadModule);
          }
          metadata.neededDynlibs.forEach(needed => loadDynamicLibrary(needed, flags, localScope));
          return loadModule();
        };
        var mergeLibSymbols = (exports, libName) => {
          // add symbols into global namespace TODO: weak linking etc.
          for (var [sym, exp] of Object.entries(exports)) {
            // When RTLD_GLOBAL is enabled, the symbols defined by this shared object
            // will be made available for symbol resolution of subsequently loaded
            // shared objects.
            // We should copy the symbols (which include methods and variables) from
            // SIDE_MODULE to MAIN_MODULE.
            const setImport = target => {
              if (!isSymbolDefined(target)) {
                wasmImports[target] = exp;
              }
            };
            setImport(sym);
            // Special case for handling of main symbol:  If a side module exports
            // `main` that also acts a definition for `__main_argc_argv` and vice
            // versa.
            const main_alias = "__main_argc_argv";
            if (sym == "main") {
              setImport(main_alias);
            }
            if (sym == main_alias) {
              setImport("main");
            }
            if (sym.startsWith("dynCall_") && !Module.hasOwnProperty(sym)) {
              Module[sym] = exp;
            }
          }
        };
        /** @param {boolean=} noRunDep */ var asyncLoad = (url, onload, onerror, noRunDep) => {
          var dep = !noRunDep ? getUniqueRunDependency(`al ${url}`) : "";
          readAsync(url).then(arrayBuffer => {
            onload(new Uint8Array(arrayBuffer));
            if (dep) removeRunDependency();
          }, err => {
            if (onerror) {
              onerror();
            } else {
              throw `Loading data file "${url}" failed.`;
            }
          });
          if (dep) addRunDependency();
        };
        /**
       * @param {number=} handle
       * @param {Object=} localScope
       */ function loadDynamicLibrary(libName, flags = {
          global: true,
          nodelete: true
        }, localScope, handle) {
          // when loadDynamicLibrary did not have flags, libraries were loaded
          // globally & permanently
          var dso = LDSO.loadedLibsByName[libName];
          if (dso) {
            // the library is being loaded or has been loaded already.
            if (!flags.global) {
              if (localScope) {
                Object.assign(localScope, dso.exports);
              }
            } else if (!dso.global) {
              // The library was previously loaded only locally but not
              // we have a request with global=true.
              dso.global = true;
              mergeLibSymbols(dso.exports);
            }
            // same for "nodelete"
            if (flags.nodelete && dso.refcount !== Infinity) {
              dso.refcount = Infinity;
            }
            dso.refcount++;
            if (handle) {
              LDSO.loadedLibsByHandle[handle] = dso;
            }
            return flags.loadAsync ? Promise.resolve(true) : true;
          }
          // allocate new DSO
          dso = newDSO(libName, handle, "loading");
          dso.refcount = flags.nodelete ? Infinity : 1;
          dso.global = flags.global;
          // libName -> libData
          function loadLibData() {
            // for wasm, we can use fetch for async, but for fs mode we can only imitate it
            if (handle) {
              var data = LE_HEAP_LOAD_U32((((handle) + (28)) >> 2) * 4);
              var dataSize = LE_HEAP_LOAD_U32((((handle) + (32)) >> 2) * 4);
              if (data && dataSize) {
                var libData = HEAP8.slice(data, data + dataSize);
                return flags.loadAsync ? Promise.resolve(libData) : libData;
              }
            }
            var libFile = locateFile(libName);
            if (flags.loadAsync) {
              return new Promise(function(resolve, reject) {
                asyncLoad(libFile, resolve, reject);
              });
            }
            // load the binary synchronously
            if (!readBinary) {
              throw new Error(`${libFile}: file not found, and synchronous loading of external files is not available`);
            }
            return readBinary(libFile);
          }
          // libName -> exports
          function getExports() {
            // module not preloaded - load lib data and create new module from it
            if (flags.loadAsync) {
              return loadLibData().then(libData => loadWebAssemblyModule(libData, flags, libName, localScope, handle));
            }
            return loadWebAssemblyModule(loadLibData(), flags, libName, localScope, handle);
          }
          // module for lib is loaded - update the dso & global namespace
          function moduleLoaded(exports) {
            if (dso.global) {
              mergeLibSymbols(exports);
            } else if (localScope) {
              Object.assign(localScope, exports);
            }
            dso.exports = exports;
          }
          if (flags.loadAsync) {
            return getExports().then(exports => {
              moduleLoaded(exports);
              return true;
            });
          }
          moduleLoaded(getExports());
          return true;
        }
        var reportUndefinedSymbols = () => {
          for (var [symName, entry] of Object.entries(GOT)) {
            if (entry.value == 0) {
              var value = resolveGlobalSymbol(symName, true).sym;
              if (!value && !entry.required) {
                // Ignore undefined symbols that are imported as weak.
                continue;
              }
              if (typeof value == "function") {
                /** @suppress {checkTypes} */ entry.value = addFunction(value, value.sig);
              } else if (typeof value == "number") {
                entry.value = value;
              } else {
                throw new Error(`bad export type for '${symName}': ${typeof value}`);
              }
            }
          }
        };
        var loadDylibs = () => {
          if (!dynamicLibraries.length) {
            reportUndefinedSymbols();
            return;
          }
          // Load binaries asynchronously
          addRunDependency();
          dynamicLibraries.reduce((chain, lib) => chain.then(() => loadDynamicLibrary(lib, {
            loadAsync: true,
            global: true,
            nodelete: true,
            allowUndefined: true
          })), Promise.resolve()).then(() => {
            // we got them all, wonderful
            reportUndefinedSymbols();
            removeRunDependency();
          });
        };
        var noExitRuntime = Module["noExitRuntime"] || true;
        /**
     * @param {number} ptr
     * @param {number} value
     * @param {string} type
     */ function setValue(ptr, value, type = "i8") {
          if (type.endsWith("*")) type = "*";
          switch (type) {
           case "i1":
            HEAP8[ptr] = value;
            break;

           case "i8":
            HEAP8[ptr] = value;
            break;

           case "i16":
            LE_HEAP_STORE_I16(((ptr) >> 1) * 2, value);
            break;

           case "i32":
            LE_HEAP_STORE_I32(((ptr) >> 2) * 4, value);
            break;

           case "i64":
            abort("to do setValue(i64) use WASM_BIGINT");

           case "float":
            LE_HEAP_STORE_F32(((ptr) >> 2) * 4, value);
            break;

           case "double":
            LE_HEAP_STORE_F64(((ptr) >> 3) * 8, value);
            break;

           case "*":
            LE_HEAP_STORE_U32(((ptr) >> 2) * 4, value);
            break;

           default:
            abort(`invalid type for setValue: ${type}`);
          }
        }
        var ___memory_base = new WebAssembly.Global({
          "value": "i32",
          "mutable": false
        }, 1024);
        var ___stack_pointer = new WebAssembly.Global({
          "value": "i32",
          "mutable": true
        }, 78112);
        var ___table_base = new WebAssembly.Global({
          "value": "i32",
          "mutable": false
        }, 1);
        var __abort_js = () => {
          abort("");
        };
        __abort_js.sig = "v";
        var nowIsMonotonic = 1;
        var __emscripten_get_now_is_monotonic = () => nowIsMonotonic;
        __emscripten_get_now_is_monotonic.sig = "i";
        var __emscripten_memcpy_js = (dest, src, num) => HEAPU8.copyWithin(dest, src, src + num);
        __emscripten_memcpy_js.sig = "vppp";
        var _emscripten_get_now;
        // Modern environment where performance.now() is supported:
        // N.B. a shorter form "_emscripten_get_now = performance.now;" is
        // unfortunately not allowed even in current browsers (e.g. FF Nightly 75).
        _emscripten_get_now = () => performance.now();
        _emscripten_get_now.sig = "d";
        var getHeapMax = () => // Stay one Wasm page short of 4GB: while e.g. Chrome is able to allocate
        // full 4GB Wasm memories, the size will wrap back to 0 bytes in Wasm side
        // for any code that deals with heap sizes, which would require special
        // casing all heap size related code to treat 0 specially.
        2147483648;
        var growMemory = size => {
          var b = wasmMemory.buffer;
          var pages = (size - b.byteLength + 65535) / 65536;
          try {
            // round size grow request up to wasm page size (fixed 64KB per spec)
            wasmMemory.grow(pages);
            // .grow() takes a delta compared to the previous size
            updateMemoryViews();
            return 1;
          } /*success*/ catch (e) {}
        };
        // implicit 0 return to save code size (caller will cast "undefined" into 0
        // anyhow)
        var _emscripten_resize_heap = requestedSize => {
          var oldSize = HEAPU8.length;
          // With CAN_ADDRESS_2GB or MEMORY64, pointers are already unsigned.
          requestedSize >>>= 0;
          // With multithreaded builds, races can happen (another thread might increase the size
          // in between), so return a failure, and let the caller retry.
          // Memory resize rules:
          // 1.  Always increase heap size to at least the requested size, rounded up
          //     to next page multiple.
          // 2a. If MEMORY_GROWTH_LINEAR_STEP == -1, excessively resize the heap
          //     geometrically: increase the heap size according to
          //     MEMORY_GROWTH_GEOMETRIC_STEP factor (default +20%), At most
          //     overreserve by MEMORY_GROWTH_GEOMETRIC_CAP bytes (default 96MB).
          // 2b. If MEMORY_GROWTH_LINEAR_STEP != -1, excessively resize the heap
          //     linearly: increase the heap size by at least
          //     MEMORY_GROWTH_LINEAR_STEP bytes.
          // 3.  Max size for the heap is capped at 2048MB-WASM_PAGE_SIZE, or by
          //     MAXIMUM_MEMORY, or by ASAN limit, depending on which is smallest
          // 4.  If we were unable to allocate as much memory, it may be due to
          //     over-eager decision to excessively reserve due to (3) above.
          //     Hence if an allocation fails, cut down on the amount of excess
          //     growth, in an attempt to succeed to perform a smaller allocation.
          // A limit is set for how much we can grow. We should not exceed that
          // (the wasm binary specifies it, so if we tried, we'd fail anyhow).
          var maxHeapSize = getHeapMax();
          if (requestedSize > maxHeapSize) {
            return false;
          }
          var alignUp = (x, multiple) => x + (multiple - x % multiple) % multiple;
          // Loop through potential heap size increases. If we attempt a too eager
          // reservation that fails, cut down on the attempted size and reserve a
          // smaller bump instead. (max 3 times, chosen somewhat arbitrarily)
          for (var cutDown = 1; cutDown <= 4; cutDown *= 2) {
            var overGrownHeapSize = oldSize * (1 + .2 / cutDown);
            // ensure geometric growth
            // but limit overreserving (default to capping at +96MB overgrowth at most)
            overGrownHeapSize = Math.min(overGrownHeapSize, requestedSize + 100663296);
            var newSize = Math.min(maxHeapSize, alignUp(Math.max(requestedSize, overGrownHeapSize), 65536));
            var replacement = growMemory(newSize);
            if (replacement) {
              return true;
            }
          }
          return false;
        };
        _emscripten_resize_heap.sig = "ip";
        var _fd_close = fd => 52;
        _fd_close.sig = "ii";
        function _fd_seek(fd, offset_low, offset_high, whence, newOffset) {
          return 70;
        }
        _fd_seek.sig = "iiiiip";
        var printCharBuffers = [ null, [], [] ];
        var printChar = (stream, curr) => {
          var buffer = printCharBuffers[stream];
          if (curr === 0 || curr === 10) {
            (stream === 1 ? out : err)(UTF8ArrayToString(buffer, 0));
            buffer.length = 0;
          } else {
            buffer.push(curr);
          }
        };
        var _fd_write = (fd, iov, iovcnt, pnum) => {
          // hack to support printf in SYSCALLS_REQUIRE_FILESYSTEM=0
          var num = 0;
          for (var i = 0; i < iovcnt; i++) {
            var ptr = LE_HEAP_LOAD_U32(((iov) >> 2) * 4);
            var len = LE_HEAP_LOAD_U32((((iov) + (4)) >> 2) * 4);
            iov += 8;
            for (var j = 0; j < len; j++) {
              printChar(fd, HEAPU8[ptr + j]);
            }
            num += len;
          }
          LE_HEAP_STORE_U32(((pnum) >> 2) * 4, num);
          return 0;
        };
        _fd_write.sig = "iippp";
        function _tree_sitter_log_callback(isLexMessage, messageAddress) {
          if (currentLogCallback) {
            const message = UTF8ToString(messageAddress);
            currentLogCallback(message, isLexMessage !== 0);
          }
        }
        function _tree_sitter_parse_callback(inputBufferAddress, index, row, column, lengthAddress) {
          const INPUT_BUFFER_SIZE = 10 * 1024;
          const string = currentParseCallback(index, {
            row: row,
            column: column
          });
          if (typeof string === "string") {
            setValue(lengthAddress, string.length, "i32");
            stringToUTF16(string, inputBufferAddress, INPUT_BUFFER_SIZE);
          } else {
            setValue(lengthAddress, 0, "i32");
          }
        }
        var runtimeKeepaliveCounter = 0;
        var keepRuntimeAlive = () => noExitRuntime || runtimeKeepaliveCounter > 0;
        var _proc_exit = code => {
          EXITSTATUS = code;
          if (!keepRuntimeAlive()) {
            Module["onExit"]?.(code);
            ABORT = true;
          }
          quit_(code, new ExitStatus(code));
        };
        _proc_exit.sig = "vi";
        /** @param {boolean|number=} implicit */ var exitJS = (status, implicit) => {
          EXITSTATUS = status;
          _proc_exit(status);
        };
        var handleException = e => {
          // Certain exception types we do not treat as errors since they are used for
          // internal control flow.
          // 1. ExitStatus, which is thrown by exit()
          // 2. "unwind", which is thrown by emscripten_unwind_to_js_event_loop() and others
          //    that wish to return to JS event loop.
          if (e instanceof ExitStatus || e == "unwind") {
            return EXITSTATUS;
          }
          quit_(1, e);
        };
        var lengthBytesUTF8 = str => {
          var len = 0;
          for (var i = 0; i < str.length; ++i) {
            // Gotcha: charCodeAt returns a 16-bit word that is a UTF-16 encoded code
            // unit, not a Unicode code point of the character! So decode
            // UTF16->UTF32->UTF8.
            // See http://unicode.org/faq/utf_bom.html#utf16-3
            var c = str.charCodeAt(i);
            // possibly a lead surrogate
            if (c <= 127) {
              len++;
            } else if (c <= 2047) {
              len += 2;
            } else if (c >= 55296 && c <= 57343) {
              len += 4;
              ++i;
            } else {
              len += 3;
            }
          }
          return len;
        };
        var stringToUTF8Array = (str, heap, outIdx, maxBytesToWrite) => {
          // Parameter maxBytesToWrite is not optional. Negative values, 0, null,
          // undefined and false each don't write out any bytes.
          if (!(maxBytesToWrite > 0)) return 0;
          var startIdx = outIdx;
          var endIdx = outIdx + maxBytesToWrite - 1;
          // -1 for string null terminator.
          for (var i = 0; i < str.length; ++i) {
            // Gotcha: charCodeAt returns a 16-bit word that is a UTF-16 encoded code
            // unit, not a Unicode code point of the character! So decode
            // UTF16->UTF32->UTF8.
            // See http://unicode.org/faq/utf_bom.html#utf16-3
            // For UTF8 byte structure, see http://en.wikipedia.org/wiki/UTF-8#Description
            // and https://www.ietf.org/rfc/rfc2279.txt
            // and https://tools.ietf.org/html/rfc3629
            var u = str.charCodeAt(i);
            // possibly a lead surrogate
            if (u >= 55296 && u <= 57343) {
              var u1 = str.charCodeAt(++i);
              u = 65536 + ((u & 1023) << 10) | (u1 & 1023);
            }
            if (u <= 127) {
              if (outIdx >= endIdx) break;
              heap[outIdx++] = u;
            } else if (u <= 2047) {
              if (outIdx + 1 >= endIdx) break;
              heap[outIdx++] = 192 | (u >> 6);
              heap[outIdx++] = 128 | (u & 63);
            } else if (u <= 65535) {
              if (outIdx + 2 >= endIdx) break;
              heap[outIdx++] = 224 | (u >> 12);
              heap[outIdx++] = 128 | ((u >> 6) & 63);
              heap[outIdx++] = 128 | (u & 63);
            } else {
              if (outIdx + 3 >= endIdx) break;
              heap[outIdx++] = 240 | (u >> 18);
              heap[outIdx++] = 128 | ((u >> 12) & 63);
              heap[outIdx++] = 128 | ((u >> 6) & 63);
              heap[outIdx++] = 128 | (u & 63);
            }
          }
          // Null-terminate the pointer to the buffer.
          heap[outIdx] = 0;
          return outIdx - startIdx;
        };
        var stringToUTF8 = (str, outPtr, maxBytesToWrite) => stringToUTF8Array(str, HEAPU8, outPtr, maxBytesToWrite);
        var stackAlloc = sz => __emscripten_stack_alloc(sz);
        var stringToUTF8OnStack = str => {
          var size = lengthBytesUTF8(str) + 1;
          var ret = stackAlloc(size);
          stringToUTF8(str, ret, size);
          return ret;
        };
        var stringToUTF16 = (str, outPtr, maxBytesToWrite) => {
          // Backwards compatibility: if max bytes is not specified, assume unsafe unbounded write is allowed.
          maxBytesToWrite ??= 2147483647;
          if (maxBytesToWrite < 2) return 0;
          maxBytesToWrite -= 2;
          // Null terminator.
          var startPtr = outPtr;
          var numCharsToWrite = (maxBytesToWrite < str.length * 2) ? (maxBytesToWrite / 2) : str.length;
          for (var i = 0; i < numCharsToWrite; ++i) {
            // charCodeAt returns a UTF-16 encoded code unit, so it can be directly written to the HEAP.
            var codeUnit = str.charCodeAt(i);
            // possibly a lead surrogate
            LE_HEAP_STORE_I16(((outPtr) >> 1) * 2, codeUnit);
            outPtr += 2;
          }
          // Null-terminate the pointer to the HEAP.
          LE_HEAP_STORE_I16(((outPtr) >> 1) * 2, 0);
          return outPtr - startPtr;
        };
        var AsciiToString = ptr => {
          var str = "";
          while (1) {
            var ch = HEAPU8[ptr++];
            if (!ch) return str;
            str += String.fromCharCode(ch);
          }
        };
        var wasmImports = {
          /** @export */ __heap_base: ___heap_base,
          /** @export */ __indirect_function_table: wasmTable,
          /** @export */ __memory_base: ___memory_base,
          /** @export */ __stack_pointer: ___stack_pointer,
          /** @export */ __table_base: ___table_base,
          /** @export */ _abort_js: __abort_js,
          /** @export */ _emscripten_get_now_is_monotonic: __emscripten_get_now_is_monotonic,
          /** @export */ _emscripten_memcpy_js: __emscripten_memcpy_js,
          /** @export */ emscripten_get_now: _emscripten_get_now,
          /** @export */ emscripten_resize_heap: _emscripten_resize_heap,
          /** @export */ fd_close: _fd_close,
          /** @export */ fd_seek: _fd_seek,
          /** @export */ fd_write: _fd_write,
          /** @export */ memory: wasmMemory,
          /** @export */ tree_sitter_log_callback: _tree_sitter_log_callback,
          /** @export */ tree_sitter_parse_callback: _tree_sitter_parse_callback
        };
        var wasmExports = createWasm();
        var _malloc = Module["_malloc"] = a0 => (_malloc = Module["_malloc"] = wasmExports["malloc"])(a0);
        Module["_calloc"] = (a0, a1) => (Module["_calloc"] = wasmExports["calloc"])(a0, a1);
        Module["_realloc"] = (a0, a1) => (Module["_realloc"] = wasmExports["realloc"])(a0, a1);
        Module["_free"] = a0 => (Module["_free"] = wasmExports["free"])(a0);
        Module["_ts_language_symbol_count"] = a0 => (Module["_ts_language_symbol_count"] = wasmExports["ts_language_symbol_count"])(a0);
        Module["_ts_language_state_count"] = a0 => (Module["_ts_language_state_count"] = wasmExports["ts_language_state_count"])(a0);
        Module["_ts_language_version"] = a0 => (Module["_ts_language_version"] = wasmExports["ts_language_version"])(a0);
        Module["_ts_language_field_count"] = a0 => (Module["_ts_language_field_count"] = wasmExports["ts_language_field_count"])(a0);
        Module["_ts_language_next_state"] = (a0, a1, a2) => (Module["_ts_language_next_state"] = wasmExports["ts_language_next_state"])(a0, a1, a2);
        Module["_ts_language_symbol_name"] = (a0, a1) => (Module["_ts_language_symbol_name"] = wasmExports["ts_language_symbol_name"])(a0, a1);
        Module["_ts_language_symbol_for_name"] = (a0, a1, a2, a3) => (Module["_ts_language_symbol_for_name"] = wasmExports["ts_language_symbol_for_name"])(a0, a1, a2, a3);
        Module["_strncmp"] = (a0, a1, a2) => (Module["_strncmp"] = wasmExports["strncmp"])(a0, a1, a2);
        Module["_ts_language_symbol_type"] = (a0, a1) => (Module["_ts_language_symbol_type"] = wasmExports["ts_language_symbol_type"])(a0, a1);
        Module["_ts_language_field_name_for_id"] = (a0, a1) => (Module["_ts_language_field_name_for_id"] = wasmExports["ts_language_field_name_for_id"])(a0, a1);
        Module["_ts_lookahead_iterator_new"] = (a0, a1) => (Module["_ts_lookahead_iterator_new"] = wasmExports["ts_lookahead_iterator_new"])(a0, a1);
        Module["_ts_lookahead_iterator_delete"] = a0 => (Module["_ts_lookahead_iterator_delete"] = wasmExports["ts_lookahead_iterator_delete"])(a0);
        Module["_ts_lookahead_iterator_reset_state"] = (a0, a1) => (Module["_ts_lookahead_iterator_reset_state"] = wasmExports["ts_lookahead_iterator_reset_state"])(a0, a1);
        Module["_ts_lookahead_iterator_reset"] = (a0, a1, a2) => (Module["_ts_lookahead_iterator_reset"] = wasmExports["ts_lookahead_iterator_reset"])(a0, a1, a2);
        Module["_ts_lookahead_iterator_next"] = a0 => (Module["_ts_lookahead_iterator_next"] = wasmExports["ts_lookahead_iterator_next"])(a0);
        Module["_ts_lookahead_iterator_current_symbol"] = a0 => (Module["_ts_lookahead_iterator_current_symbol"] = wasmExports["ts_lookahead_iterator_current_symbol"])(a0);
        Module["_memset"] = (a0, a1, a2) => (Module["_memset"] = wasmExports["memset"])(a0, a1, a2);
        Module["_memcpy"] = (a0, a1, a2) => (Module["_memcpy"] = wasmExports["memcpy"])(a0, a1, a2);
        Module["_ts_parser_delete"] = a0 => (Module["_ts_parser_delete"] = wasmExports["ts_parser_delete"])(a0);
        Module["_ts_parser_reset"] = a0 => (Module["_ts_parser_reset"] = wasmExports["ts_parser_reset"])(a0);
        Module["_ts_parser_set_language"] = (a0, a1) => (Module["_ts_parser_set_language"] = wasmExports["ts_parser_set_language"])(a0, a1);
        Module["_ts_parser_timeout_micros"] = a0 => (Module["_ts_parser_timeout_micros"] = wasmExports["ts_parser_timeout_micros"])(a0);
        Module["_ts_parser_set_timeout_micros"] = (a0, a1, a2) => (Module["_ts_parser_set_timeout_micros"] = wasmExports["ts_parser_set_timeout_micros"])(a0, a1, a2);
        Module["_ts_parser_set_included_ranges"] = (a0, a1, a2) => (Module["_ts_parser_set_included_ranges"] = wasmExports["ts_parser_set_included_ranges"])(a0, a1, a2);
        Module["_memmove"] = (a0, a1, a2) => (Module["_memmove"] = wasmExports["memmove"])(a0, a1, a2);
        Module["_memcmp"] = (a0, a1, a2) => (Module["_memcmp"] = wasmExports["memcmp"])(a0, a1, a2);
        Module["_ts_query_new"] = (a0, a1, a2, a3, a4) => (Module["_ts_query_new"] = wasmExports["ts_query_new"])(a0, a1, a2, a3, a4);
        Module["_ts_query_delete"] = a0 => (Module["_ts_query_delete"] = wasmExports["ts_query_delete"])(a0);
        Module["_iswspace"] = a0 => (Module["_iswspace"] = wasmExports["iswspace"])(a0);
        Module["_iswalnum"] = a0 => (Module["_iswalnum"] = wasmExports["iswalnum"])(a0);
        Module["_ts_query_pattern_count"] = a0 => (Module["_ts_query_pattern_count"] = wasmExports["ts_query_pattern_count"])(a0);
        Module["_ts_query_capture_count"] = a0 => (Module["_ts_query_capture_count"] = wasmExports["ts_query_capture_count"])(a0);
        Module["_ts_query_string_count"] = a0 => (Module["_ts_query_string_count"] = wasmExports["ts_query_string_count"])(a0);
        Module["_ts_query_capture_name_for_id"] = (a0, a1, a2) => (Module["_ts_query_capture_name_for_id"] = wasmExports["ts_query_capture_name_for_id"])(a0, a1, a2);
        Module["_ts_query_string_value_for_id"] = (a0, a1, a2) => (Module["_ts_query_string_value_for_id"] = wasmExports["ts_query_string_value_for_id"])(a0, a1, a2);
        Module["_ts_query_predicates_for_pattern"] = (a0, a1, a2) => (Module["_ts_query_predicates_for_pattern"] = wasmExports["ts_query_predicates_for_pattern"])(a0, a1, a2);
        Module["_ts_query_disable_capture"] = (a0, a1, a2) => (Module["_ts_query_disable_capture"] = wasmExports["ts_query_disable_capture"])(a0, a1, a2);
        Module["_ts_tree_copy"] = a0 => (Module["_ts_tree_copy"] = wasmExports["ts_tree_copy"])(a0);
        Module["_ts_tree_delete"] = a0 => (Module["_ts_tree_delete"] = wasmExports["ts_tree_delete"])(a0);
        Module["_ts_init"] = () => (Module["_ts_init"] = wasmExports["ts_init"])();
        Module["_ts_parser_new_wasm"] = () => (Module["_ts_parser_new_wasm"] = wasmExports["ts_parser_new_wasm"])();
        Module["_ts_parser_enable_logger_wasm"] = (a0, a1) => (Module["_ts_parser_enable_logger_wasm"] = wasmExports["ts_parser_enable_logger_wasm"])(a0, a1);
        Module["_ts_parser_parse_wasm"] = (a0, a1, a2, a3, a4) => (Module["_ts_parser_parse_wasm"] = wasmExports["ts_parser_parse_wasm"])(a0, a1, a2, a3, a4);
        Module["_ts_parser_included_ranges_wasm"] = a0 => (Module["_ts_parser_included_ranges_wasm"] = wasmExports["ts_parser_included_ranges_wasm"])(a0);
        Module["_ts_language_type_is_named_wasm"] = (a0, a1) => (Module["_ts_language_type_is_named_wasm"] = wasmExports["ts_language_type_is_named_wasm"])(a0, a1);
        Module["_ts_language_type_is_visible_wasm"] = (a0, a1) => (Module["_ts_language_type_is_visible_wasm"] = wasmExports["ts_language_type_is_visible_wasm"])(a0, a1);
        Module["_ts_tree_root_node_wasm"] = a0 => (Module["_ts_tree_root_node_wasm"] = wasmExports["ts_tree_root_node_wasm"])(a0);
        Module["_ts_tree_root_node_with_offset_wasm"] = a0 => (Module["_ts_tree_root_node_with_offset_wasm"] = wasmExports["ts_tree_root_node_with_offset_wasm"])(a0);
        Module["_ts_tree_edit_wasm"] = a0 => (Module["_ts_tree_edit_wasm"] = wasmExports["ts_tree_edit_wasm"])(a0);
        Module["_ts_tree_included_ranges_wasm"] = a0 => (Module["_ts_tree_included_ranges_wasm"] = wasmExports["ts_tree_included_ranges_wasm"])(a0);
        Module["_ts_tree_get_changed_ranges_wasm"] = (a0, a1) => (Module["_ts_tree_get_changed_ranges_wasm"] = wasmExports["ts_tree_get_changed_ranges_wasm"])(a0, a1);
        Module["_ts_tree_cursor_new_wasm"] = a0 => (Module["_ts_tree_cursor_new_wasm"] = wasmExports["ts_tree_cursor_new_wasm"])(a0);
        Module["_ts_tree_cursor_delete_wasm"] = a0 => (Module["_ts_tree_cursor_delete_wasm"] = wasmExports["ts_tree_cursor_delete_wasm"])(a0);
        Module["_ts_tree_cursor_reset_wasm"] = a0 => (Module["_ts_tree_cursor_reset_wasm"] = wasmExports["ts_tree_cursor_reset_wasm"])(a0);
        Module["_ts_tree_cursor_reset_to_wasm"] = (a0, a1) => (Module["_ts_tree_cursor_reset_to_wasm"] = wasmExports["ts_tree_cursor_reset_to_wasm"])(a0, a1);
        Module["_ts_tree_cursor_goto_first_child_wasm"] = a0 => (Module["_ts_tree_cursor_goto_first_child_wasm"] = wasmExports["ts_tree_cursor_goto_first_child_wasm"])(a0);
        Module["_ts_tree_cursor_goto_last_child_wasm"] = a0 => (Module["_ts_tree_cursor_goto_last_child_wasm"] = wasmExports["ts_tree_cursor_goto_last_child_wasm"])(a0);
        Module["_ts_tree_cursor_goto_first_child_for_index_wasm"] = a0 => (Module["_ts_tree_cursor_goto_first_child_for_index_wasm"] = wasmExports["ts_tree_cursor_goto_first_child_for_index_wasm"])(a0);
        Module["_ts_tree_cursor_goto_first_child_for_position_wasm"] = a0 => (Module["_ts_tree_cursor_goto_first_child_for_position_wasm"] = wasmExports["ts_tree_cursor_goto_first_child_for_position_wasm"])(a0);
        Module["_ts_tree_cursor_goto_next_sibling_wasm"] = a0 => (Module["_ts_tree_cursor_goto_next_sibling_wasm"] = wasmExports["ts_tree_cursor_goto_next_sibling_wasm"])(a0);
        Module["_ts_tree_cursor_goto_previous_sibling_wasm"] = a0 => (Module["_ts_tree_cursor_goto_previous_sibling_wasm"] = wasmExports["ts_tree_cursor_goto_previous_sibling_wasm"])(a0);
        Module["_ts_tree_cursor_goto_descendant_wasm"] = (a0, a1) => (Module["_ts_tree_cursor_goto_descendant_wasm"] = wasmExports["ts_tree_cursor_goto_descendant_wasm"])(a0, a1);
        Module["_ts_tree_cursor_goto_parent_wasm"] = a0 => (Module["_ts_tree_cursor_goto_parent_wasm"] = wasmExports["ts_tree_cursor_goto_parent_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_type_id_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_type_id_wasm"] = wasmExports["ts_tree_cursor_current_node_type_id_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_state_id_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_state_id_wasm"] = wasmExports["ts_tree_cursor_current_node_state_id_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_is_named_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_is_named_wasm"] = wasmExports["ts_tree_cursor_current_node_is_named_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_is_missing_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_is_missing_wasm"] = wasmExports["ts_tree_cursor_current_node_is_missing_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_id_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_id_wasm"] = wasmExports["ts_tree_cursor_current_node_id_wasm"])(a0);
        Module["_ts_tree_cursor_start_position_wasm"] = a0 => (Module["_ts_tree_cursor_start_position_wasm"] = wasmExports["ts_tree_cursor_start_position_wasm"])(a0);
        Module["_ts_tree_cursor_end_position_wasm"] = a0 => (Module["_ts_tree_cursor_end_position_wasm"] = wasmExports["ts_tree_cursor_end_position_wasm"])(a0);
        Module["_ts_tree_cursor_start_index_wasm"] = a0 => (Module["_ts_tree_cursor_start_index_wasm"] = wasmExports["ts_tree_cursor_start_index_wasm"])(a0);
        Module["_ts_tree_cursor_end_index_wasm"] = a0 => (Module["_ts_tree_cursor_end_index_wasm"] = wasmExports["ts_tree_cursor_end_index_wasm"])(a0);
        Module["_ts_tree_cursor_current_field_id_wasm"] = a0 => (Module["_ts_tree_cursor_current_field_id_wasm"] = wasmExports["ts_tree_cursor_current_field_id_wasm"])(a0);
        Module["_ts_tree_cursor_current_depth_wasm"] = a0 => (Module["_ts_tree_cursor_current_depth_wasm"] = wasmExports["ts_tree_cursor_current_depth_wasm"])(a0);
        Module["_ts_tree_cursor_current_descendant_index_wasm"] = a0 => (Module["_ts_tree_cursor_current_descendant_index_wasm"] = wasmExports["ts_tree_cursor_current_descendant_index_wasm"])(a0);
        Module["_ts_tree_cursor_current_node_wasm"] = a0 => (Module["_ts_tree_cursor_current_node_wasm"] = wasmExports["ts_tree_cursor_current_node_wasm"])(a0);
        Module["_ts_node_symbol_wasm"] = a0 => (Module["_ts_node_symbol_wasm"] = wasmExports["ts_node_symbol_wasm"])(a0);
        Module["_ts_node_field_name_for_child_wasm"] = (a0, a1) => (Module["_ts_node_field_name_for_child_wasm"] = wasmExports["ts_node_field_name_for_child_wasm"])(a0, a1);
        Module["_ts_node_children_by_field_id_wasm"] = (a0, a1) => (Module["_ts_node_children_by_field_id_wasm"] = wasmExports["ts_node_children_by_field_id_wasm"])(a0, a1);
        Module["_ts_node_first_child_for_byte_wasm"] = a0 => (Module["_ts_node_first_child_for_byte_wasm"] = wasmExports["ts_node_first_child_for_byte_wasm"])(a0);
        Module["_ts_node_first_named_child_for_byte_wasm"] = a0 => (Module["_ts_node_first_named_child_for_byte_wasm"] = wasmExports["ts_node_first_named_child_for_byte_wasm"])(a0);
        Module["_ts_node_grammar_symbol_wasm"] = a0 => (Module["_ts_node_grammar_symbol_wasm"] = wasmExports["ts_node_grammar_symbol_wasm"])(a0);
        Module["_ts_node_child_count_wasm"] = a0 => (Module["_ts_node_child_count_wasm"] = wasmExports["ts_node_child_count_wasm"])(a0);
        Module["_ts_node_named_child_count_wasm"] = a0 => (Module["_ts_node_named_child_count_wasm"] = wasmExports["ts_node_named_child_count_wasm"])(a0);
        Module["_ts_node_child_wasm"] = (a0, a1) => (Module["_ts_node_child_wasm"] = wasmExports["ts_node_child_wasm"])(a0, a1);
        Module["_ts_node_named_child_wasm"] = (a0, a1) => (Module["_ts_node_named_child_wasm"] = wasmExports["ts_node_named_child_wasm"])(a0, a1);
        Module["_ts_node_child_by_field_id_wasm"] = (a0, a1) => (Module["_ts_node_child_by_field_id_wasm"] = wasmExports["ts_node_child_by_field_id_wasm"])(a0, a1);
        Module["_ts_node_next_sibling_wasm"] = a0 => (Module["_ts_node_next_sibling_wasm"] = wasmExports["ts_node_next_sibling_wasm"])(a0);
        Module["_ts_node_prev_sibling_wasm"] = a0 => (Module["_ts_node_prev_sibling_wasm"] = wasmExports["ts_node_prev_sibling_wasm"])(a0);
        Module["_ts_node_next_named_sibling_wasm"] = a0 => (Module["_ts_node_next_named_sibling_wasm"] = wasmExports["ts_node_next_named_sibling_wasm"])(a0);
        Module["_ts_node_prev_named_sibling_wasm"] = a0 => (Module["_ts_node_prev_named_sibling_wasm"] = wasmExports["ts_node_prev_named_sibling_wasm"])(a0);
        Module["_ts_node_descendant_count_wasm"] = a0 => (Module["_ts_node_descendant_count_wasm"] = wasmExports["ts_node_descendant_count_wasm"])(a0);
        Module["_ts_node_parent_wasm"] = a0 => (Module["_ts_node_parent_wasm"] = wasmExports["ts_node_parent_wasm"])(a0);
        Module["_ts_node_descendant_for_index_wasm"] = a0 => (Module["_ts_node_descendant_for_index_wasm"] = wasmExports["ts_node_descendant_for_index_wasm"])(a0);
        Module["_ts_node_named_descendant_for_index_wasm"] = a0 => (Module["_ts_node_named_descendant_for_index_wasm"] = wasmExports["ts_node_named_descendant_for_index_wasm"])(a0);
        Module["_ts_node_descendant_for_position_wasm"] = a0 => (Module["_ts_node_descendant_for_position_wasm"] = wasmExports["ts_node_descendant_for_position_wasm"])(a0);
        Module["_ts_node_named_descendant_for_position_wasm"] = a0 => (Module["_ts_node_named_descendant_for_position_wasm"] = wasmExports["ts_node_named_descendant_for_position_wasm"])(a0);
        Module["_ts_node_start_point_wasm"] = a0 => (Module["_ts_node_start_point_wasm"] = wasmExports["ts_node_start_point_wasm"])(a0);
        Module["_ts_node_end_point_wasm"] = a0 => (Module["_ts_node_end_point_wasm"] = wasmExports["ts_node_end_point_wasm"])(a0);
        Module["_ts_node_start_index_wasm"] = a0 => (Module["_ts_node_start_index_wasm"] = wasmExports["ts_node_start_index_wasm"])(a0);
        Module["_ts_node_end_index_wasm"] = a0 => (Module["_ts_node_end_index_wasm"] = wasmExports["ts_node_end_index_wasm"])(a0);
        Module["_ts_node_to_string_wasm"] = a0 => (Module["_ts_node_to_string_wasm"] = wasmExports["ts_node_to_string_wasm"])(a0);
        Module["_ts_node_children_wasm"] = a0 => (Module["_ts_node_children_wasm"] = wasmExports["ts_node_children_wasm"])(a0);
        Module["_ts_node_named_children_wasm"] = a0 => (Module["_ts_node_named_children_wasm"] = wasmExports["ts_node_named_children_wasm"])(a0);
        Module["_ts_node_descendants_of_type_wasm"] = (a0, a1, a2, a3, a4, a5, a6) => (Module["_ts_node_descendants_of_type_wasm"] = wasmExports["ts_node_descendants_of_type_wasm"])(a0, a1, a2, a3, a4, a5, a6);
        Module["_ts_node_is_named_wasm"] = a0 => (Module["_ts_node_is_named_wasm"] = wasmExports["ts_node_is_named_wasm"])(a0);
        Module["_ts_node_has_changes_wasm"] = a0 => (Module["_ts_node_has_changes_wasm"] = wasmExports["ts_node_has_changes_wasm"])(a0);
        Module["_ts_node_has_error_wasm"] = a0 => (Module["_ts_node_has_error_wasm"] = wasmExports["ts_node_has_error_wasm"])(a0);
        Module["_ts_node_is_error_wasm"] = a0 => (Module["_ts_node_is_error_wasm"] = wasmExports["ts_node_is_error_wasm"])(a0);
        Module["_ts_node_is_missing_wasm"] = a0 => (Module["_ts_node_is_missing_wasm"] = wasmExports["ts_node_is_missing_wasm"])(a0);
        Module["_ts_node_is_extra_wasm"] = a0 => (Module["_ts_node_is_extra_wasm"] = wasmExports["ts_node_is_extra_wasm"])(a0);
        Module["_ts_node_parse_state_wasm"] = a0 => (Module["_ts_node_parse_state_wasm"] = wasmExports["ts_node_parse_state_wasm"])(a0);
        Module["_ts_node_next_parse_state_wasm"] = a0 => (Module["_ts_node_next_parse_state_wasm"] = wasmExports["ts_node_next_parse_state_wasm"])(a0);
        Module["_ts_query_matches_wasm"] = (a0, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10) => (Module["_ts_query_matches_wasm"] = wasmExports["ts_query_matches_wasm"])(a0, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10);
        Module["_ts_query_captures_wasm"] = (a0, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10) => (Module["_ts_query_captures_wasm"] = wasmExports["ts_query_captures_wasm"])(a0, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10);
        Module["_iswalpha"] = a0 => (Module["_iswalpha"] = wasmExports["iswalpha"])(a0);
        Module["_iswblank"] = a0 => (Module["_iswblank"] = wasmExports["iswblank"])(a0);
        Module["_iswdigit"] = a0 => (Module["_iswdigit"] = wasmExports["iswdigit"])(a0);
        Module["_iswlower"] = a0 => (Module["_iswlower"] = wasmExports["iswlower"])(a0);
        Module["_iswupper"] = a0 => (Module["_iswupper"] = wasmExports["iswupper"])(a0);
        Module["_iswxdigit"] = a0 => (Module["_iswxdigit"] = wasmExports["iswxdigit"])(a0);
        Module["_memchr"] = (a0, a1, a2) => (Module["_memchr"] = wasmExports["memchr"])(a0, a1, a2);
        Module["_strlen"] = a0 => (Module["_strlen"] = wasmExports["strlen"])(a0);
        Module["_strcmp"] = (a0, a1) => (Module["_strcmp"] = wasmExports["strcmp"])(a0, a1);
        Module["_strncat"] = (a0, a1, a2) => (Module["_strncat"] = wasmExports["strncat"])(a0, a1, a2);
        Module["_strncpy"] = (a0, a1, a2) => (Module["_strncpy"] = wasmExports["strncpy"])(a0, a1, a2);
        Module["_towlower"] = a0 => (Module["_towlower"] = wasmExports["towlower"])(a0);
        Module["_towupper"] = a0 => (Module["_towupper"] = wasmExports["towupper"])(a0);
        var _setThrew = (a0, a1) => (_setThrew = wasmExports["setThrew"])(a0, a1);
        var __emscripten_stack_restore = a0 => (__emscripten_stack_restore = wasmExports["_emscripten_stack_restore"])(a0);
        var __emscripten_stack_alloc = a0 => (__emscripten_stack_alloc = wasmExports["_emscripten_stack_alloc"])(a0);
        var _emscripten_stack_get_current = () => (_emscripten_stack_get_current = wasmExports["emscripten_stack_get_current"])();
        Module["dynCall_jiji"] = (a0, a1, a2, a3, a4) => (Module["dynCall_jiji"] = wasmExports["dynCall_jiji"])(a0, a1, a2, a3, a4);
        Module["_orig$ts_parser_timeout_micros"] = a0 => (Module["_orig$ts_parser_timeout_micros"] = wasmExports["orig$ts_parser_timeout_micros"])(a0);
        Module["_orig$ts_parser_set_timeout_micros"] = (a0, a1) => (Module["_orig$ts_parser_set_timeout_micros"] = wasmExports["orig$ts_parser_set_timeout_micros"])(a0, a1);
        // include: postamble.js
        // === Auto-generated postamble setup entry stuff ===
        Module["AsciiToString"] = AsciiToString;
        Module["stringToUTF16"] = stringToUTF16;
        var calledRun;
        dependenciesFulfilled = function runCaller() {
          // If run has never been called, and we should call run (INVOKE_RUN is true, and Module.noInitialRun is not false)
          if (!calledRun) run();
          if (!calledRun) dependenciesFulfilled = runCaller;
        };
        // try this again later, after new deps are fulfilled
        function callMain(args = []) {
          var entryFunction = resolveGlobalSymbol("main").sym;
          // Main modules can't tell if they have main() at compile time, since it may
          // arrive from a dynamic library.
          if (!entryFunction) return;
          args.unshift(thisProgram);
          var argc = args.length;
          var argv = stackAlloc((argc + 1) * 4);
          var argv_ptr = argv;
          args.forEach(arg => {
            LE_HEAP_STORE_U32(((argv_ptr) >> 2) * 4, stringToUTF8OnStack(arg));
            argv_ptr += 4;
          });
          LE_HEAP_STORE_U32(((argv_ptr) >> 2) * 4, 0);
          try {
            var ret = entryFunction(argc, argv);
            // if we're not running an evented main loop, it's time to exit
            exitJS(ret, /* implicit = */ true);
            return ret;
          } catch (e) {
            return handleException(e);
          }
        }
        function run(args = arguments_) {
          if (runDependencies > 0) {
            return;
          }
          preRun();
          // a preRun added a dependency, run will be called later
          if (runDependencies > 0) {
            return;
          }
          function doRun() {
            // run may have just been called through dependencies being fulfilled just in this very frame,
            // or while the async setStatus time below was happening
            if (calledRun) return;
            calledRun = true;
            Module["calledRun"] = true;
            if (ABORT) return;
            initRuntime();
            preMain();
            Module["onRuntimeInitialized"]?.();
            if (shouldRunNow) callMain(args);
            postRun();
          }
          if (Module["setStatus"]) {
            Module["setStatus"]("Running...");
            setTimeout(function() {
              setTimeout(function() {
                Module["setStatus"]("");
              }, 1);
              doRun();
            }, 1);
          } else {
            doRun();
          }
        }
        if (Module["preInit"]) {
          if (typeof Module["preInit"] == "function") Module["preInit"] = [ Module["preInit"] ];
          while (Module["preInit"].length > 0) {
            Module["preInit"].pop()();
          }
        }
        // shouldRunNow refers to calling main(), not run().
        var shouldRunNow = true;
        if (Module["noInitialRun"]) shouldRunNow = false;
        run();
        // end include: postamble.js
        // include: /src/lib/binding_web/binding.js
        /* eslint-disable-next-line spaced-comment */ /// <reference types="emscripten" />
        /* eslint-disable-next-line spaced-comment */ /// <reference path="tree-sitter-web.d.ts"/>
        const C = Module;
        const INTERNAL = {};
        const SIZE_OF_INT = 4;
        const SIZE_OF_CURSOR = 4 * SIZE_OF_INT;
        const SIZE_OF_NODE = 5 * SIZE_OF_INT;
        const SIZE_OF_POINT = 2 * SIZE_OF_INT;
        const SIZE_OF_RANGE = 2 * SIZE_OF_INT + 2 * SIZE_OF_POINT;
        const ZERO_POINT = {
          row: 0,
          column: 0
        };
        const QUERY_WORD_REGEX = /[\w-.]*/g;
        const PREDICATE_STEP_TYPE_CAPTURE = 1;
        const PREDICATE_STEP_TYPE_STRING = 2;
        const LANGUAGE_FUNCTION_REGEX = /^_?tree_sitter_\w+/;
        let VERSION;
        let MIN_COMPATIBLE_VERSION;
        let TRANSFER_BUFFER;
        let currentParseCallback;
        // eslint-disable-next-line no-unused-vars
        let currentLogCallback;
        // eslint-disable-next-line no-unused-vars
        class ParserImpl {
          static init() {
            TRANSFER_BUFFER = C._ts_init();
            VERSION = getValue(TRANSFER_BUFFER, "i32");
            MIN_COMPATIBLE_VERSION = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
          }
          initialize() {
            C._ts_parser_new_wasm();
            this[0] = getValue(TRANSFER_BUFFER, "i32");
            this[1] = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
          }
          delete() {
            C._ts_parser_delete(this[0]);
            C._free(this[1]);
            this[0] = 0;
            this[1] = 0;
          }
          setLanguage(language) {
            let address;
            if (!language) {
              address = 0;
              language = null;
            } else if (language.constructor === Language) {
              address = language[0];
              const version = C._ts_language_version(address);
              if (version < MIN_COMPATIBLE_VERSION || VERSION < version) {
                throw new Error(`Incompatible language version ${version}. ` + `Compatibility range ${MIN_COMPATIBLE_VERSION} through ${VERSION}.`);
              }
            } else {
              throw new Error("Argument must be a Language");
            }
            this.language = language;
            C._ts_parser_set_language(this[0], address);
            return this;
          }
          getLanguage() {
            return this.language;
          }
          parse(callback, oldTree, options) {
            if (typeof callback === "string") {
              currentParseCallback = (index, _) => callback.slice(index);
            } else if (typeof callback === "function") {
              currentParseCallback = callback;
            } else {
              throw new Error("Argument must be a string or a function");
            }
            if (this.logCallback) {
              currentLogCallback = this.logCallback;
              C._ts_parser_enable_logger_wasm(this[0], 1);
            } else {
              currentLogCallback = null;
              C._ts_parser_enable_logger_wasm(this[0], 0);
            }
            let rangeCount = 0;
            let rangeAddress = 0;
            if (options?.includedRanges) {
              rangeCount = options.includedRanges.length;
              rangeAddress = C._calloc(rangeCount, SIZE_OF_RANGE);
              let address = rangeAddress;
              for (let i = 0; i < rangeCount; i++) {
                marshalRange(address, options.includedRanges[i]);
                address += SIZE_OF_RANGE;
              }
            }
            const treeAddress = C._ts_parser_parse_wasm(this[0], this[1], oldTree ? oldTree[0] : 0, rangeAddress, rangeCount);
            if (!treeAddress) {
              currentParseCallback = null;
              currentLogCallback = null;
              throw new Error("Parsing failed");
            }
            const result = new Tree(INTERNAL, treeAddress, this.language, currentParseCallback);
            currentParseCallback = null;
            currentLogCallback = null;
            return result;
          }
          reset() {
            C._ts_parser_reset(this[0]);
          }
          getIncludedRanges() {
            C._ts_parser_included_ranges_wasm(this[0]);
            const count = getValue(TRANSFER_BUFFER, "i32");
            const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const result = new Array(count);
            if (count > 0) {
              let address = buffer;
              for (let i = 0; i < count; i++) {
                result[i] = unmarshalRange(address);
                address += SIZE_OF_RANGE;
              }
              C._free(buffer);
            }
            return result;
          }
          getTimeoutMicros() {
            return C._ts_parser_timeout_micros(this[0]);
          }
          setTimeoutMicros(timeout) {
            C._ts_parser_set_timeout_micros(this[0], timeout);
          }
          setLogger(callback) {
            if (!callback) {
              callback = null;
            } else if (typeof callback !== "function") {
              throw new Error("Logger callback must be a function");
            }
            this.logCallback = callback;
            return this;
          }
          getLogger() {
            return this.logCallback;
          }
        }
        class Tree {
          constructor(internal, address, language, textCallback) {
            assertInternal(internal);
            this[0] = address;
            this.language = language;
            this.textCallback = textCallback;
          }
          copy() {
            const address = C._ts_tree_copy(this[0]);
            return new Tree(INTERNAL, address, this.language, this.textCallback);
          }
          delete() {
            C._ts_tree_delete(this[0]);
            this[0] = 0;
          }
          edit(edit) {
            marshalEdit(edit);
            C._ts_tree_edit_wasm(this[0]);
          }
          get rootNode() {
            C._ts_tree_root_node_wasm(this[0]);
            return unmarshalNode(this);
          }
          rootNodeWithOffset(offsetBytes, offsetExtent) {
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            setValue(address, offsetBytes, "i32");
            marshalPoint(address + SIZE_OF_INT, offsetExtent);
            C._ts_tree_root_node_with_offset_wasm(this[0]);
            return unmarshalNode(this);
          }
          getLanguage() {
            return this.language;
          }
          walk() {
            return this.rootNode.walk();
          }
          getChangedRanges(other) {
            if (other.constructor !== Tree) {
              throw new TypeError("Argument must be a Tree");
            }
            C._ts_tree_get_changed_ranges_wasm(this[0], other[0]);
            const count = getValue(TRANSFER_BUFFER, "i32");
            const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const result = new Array(count);
            if (count > 0) {
              let address = buffer;
              for (let i = 0; i < count; i++) {
                result[i] = unmarshalRange(address);
                address += SIZE_OF_RANGE;
              }
              C._free(buffer);
            }
            return result;
          }
          getIncludedRanges() {
            C._ts_tree_included_ranges_wasm(this[0]);
            const count = getValue(TRANSFER_BUFFER, "i32");
            const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const result = new Array(count);
            if (count > 0) {
              let address = buffer;
              for (let i = 0; i < count; i++) {
                result[i] = unmarshalRange(address);
                address += SIZE_OF_RANGE;
              }
              C._free(buffer);
            }
            return result;
          }
        }
        class Node {
          constructor(internal, tree) {
            assertInternal(internal);
            this.tree = tree;
          }
          get typeId() {
            marshalNode(this);
            return C._ts_node_symbol_wasm(this.tree[0]);
          }
          get grammarId() {
            marshalNode(this);
            return C._ts_node_grammar_symbol_wasm(this.tree[0]);
          }
          get type() {
            return this.tree.language.types[this.typeId] || "ERROR";
          }
          get grammarType() {
            return this.tree.language.types[this.grammarId] || "ERROR";
          }
          get endPosition() {
            marshalNode(this);
            C._ts_node_end_point_wasm(this.tree[0]);
            return unmarshalPoint(TRANSFER_BUFFER);
          }
          get endIndex() {
            marshalNode(this);
            return C._ts_node_end_index_wasm(this.tree[0]);
          }
          get text() {
            return getText(this.tree, this.startIndex, this.endIndex);
          }
          get parseState() {
            marshalNode(this);
            return C._ts_node_parse_state_wasm(this.tree[0]);
          }
          get nextParseState() {
            marshalNode(this);
            return C._ts_node_next_parse_state_wasm(this.tree[0]);
          }
          get isNamed() {
            marshalNode(this);
            return C._ts_node_is_named_wasm(this.tree[0]) === 1;
          }
          get hasError() {
            marshalNode(this);
            return C._ts_node_has_error_wasm(this.tree[0]) === 1;
          }
          get hasChanges() {
            marshalNode(this);
            return C._ts_node_has_changes_wasm(this.tree[0]) === 1;
          }
          get isError() {
            marshalNode(this);
            return C._ts_node_is_error_wasm(this.tree[0]) === 1;
          }
          get isMissing() {
            marshalNode(this);
            return C._ts_node_is_missing_wasm(this.tree[0]) === 1;
          }
          get isExtra() {
            marshalNode(this);
            return C._ts_node_is_extra_wasm(this.tree[0]) === 1;
          }
          equals(other) {
            return this.id === other.id;
          }
          child(index) {
            marshalNode(this);
            C._ts_node_child_wasm(this.tree[0], index);
            return unmarshalNode(this.tree);
          }
          namedChild(index) {
            marshalNode(this);
            C._ts_node_named_child_wasm(this.tree[0], index);
            return unmarshalNode(this.tree);
          }
          childForFieldId(fieldId) {
            marshalNode(this);
            C._ts_node_child_by_field_id_wasm(this.tree[0], fieldId);
            return unmarshalNode(this.tree);
          }
          childForFieldName(fieldName) {
            const fieldId = this.tree.language.fields.indexOf(fieldName);
            if (fieldId !== -1) return this.childForFieldId(fieldId);
            return null;
          }
          fieldNameForChild(index) {
            marshalNode(this);
            const address = C._ts_node_field_name_for_child_wasm(this.tree[0], index);
            if (!address) {
              return null;
            }
            const result = AsciiToString(address);
            // must not free, the string memory is owned by the language
            return result;
          }
          childrenForFieldName(fieldName) {
            const fieldId = this.tree.language.fields.indexOf(fieldName);
            if (fieldId !== -1 && fieldId !== 0) return this.childrenForFieldId(fieldId);
            return [];
          }
          childrenForFieldId(fieldId) {
            marshalNode(this);
            C._ts_node_children_by_field_id_wasm(this.tree[0], fieldId);
            const count = getValue(TRANSFER_BUFFER, "i32");
            const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const result = new Array(count);
            if (count > 0) {
              let address = buffer;
              for (let i = 0; i < count; i++) {
                result[i] = unmarshalNode(this.tree, address);
                address += SIZE_OF_NODE;
              }
              C._free(buffer);
            }
            return result;
          }
          firstChildForIndex(index) {
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            setValue(address, index, "i32");
            C._ts_node_first_child_for_byte_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          firstNamedChildForIndex(index) {
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            setValue(address, index, "i32");
            C._ts_node_first_named_child_for_byte_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get childCount() {
            marshalNode(this);
            return C._ts_node_child_count_wasm(this.tree[0]);
          }
          get namedChildCount() {
            marshalNode(this);
            return C._ts_node_named_child_count_wasm(this.tree[0]);
          }
          get firstChild() {
            return this.child(0);
          }
          get firstNamedChild() {
            return this.namedChild(0);
          }
          get lastChild() {
            return this.child(this.childCount - 1);
          }
          get lastNamedChild() {
            return this.namedChild(this.namedChildCount - 1);
          }
          get children() {
            if (!this._children) {
              marshalNode(this);
              C._ts_node_children_wasm(this.tree[0]);
              const count = getValue(TRANSFER_BUFFER, "i32");
              const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
              this._children = new Array(count);
              if (count > 0) {
                let address = buffer;
                for (let i = 0; i < count; i++) {
                  this._children[i] = unmarshalNode(this.tree, address);
                  address += SIZE_OF_NODE;
                }
                C._free(buffer);
              }
            }
            return this._children;
          }
          get namedChildren() {
            if (!this._namedChildren) {
              marshalNode(this);
              C._ts_node_named_children_wasm(this.tree[0]);
              const count = getValue(TRANSFER_BUFFER, "i32");
              const buffer = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
              this._namedChildren = new Array(count);
              if (count > 0) {
                let address = buffer;
                for (let i = 0; i < count; i++) {
                  this._namedChildren[i] = unmarshalNode(this.tree, address);
                  address += SIZE_OF_NODE;
                }
                C._free(buffer);
              }
            }
            return this._namedChildren;
          }
          descendantsOfType(types, startPosition, endPosition) {
            if (!Array.isArray(types)) types = [ types ];
            if (!startPosition) startPosition = ZERO_POINT;
            if (!endPosition) endPosition = ZERO_POINT;
            // Convert the type strings to numeric type symbols.
            const symbols = [];
            const typesBySymbol = this.tree.language.types;
            for (let i = 0, n = typesBySymbol.length; i < n; i++) {
              if (types.includes(typesBySymbol[i])) {
                symbols.push(i);
              }
            }
            // Copy the array of symbols to the WASM heap.
            const symbolsAddress = C._malloc(SIZE_OF_INT * symbols.length);
            for (let i = 0, n = symbols.length; i < n; i++) {
              setValue(symbolsAddress + i * SIZE_OF_INT, symbols[i], "i32");
            }
            // Call the C API to compute the descendants.
            marshalNode(this);
            C._ts_node_descendants_of_type_wasm(this.tree[0], symbolsAddress, symbols.length, startPosition.row, startPosition.column, endPosition.row, endPosition.column);
            // Instantiate the nodes based on the data returned.
            const descendantCount = getValue(TRANSFER_BUFFER, "i32");
            const descendantAddress = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const result = new Array(descendantCount);
            if (descendantCount > 0) {
              let address = descendantAddress;
              for (let i = 0; i < descendantCount; i++) {
                result[i] = unmarshalNode(this.tree, address);
                address += SIZE_OF_NODE;
              }
            }
            // Free the intermediate buffers
            C._free(descendantAddress);
            C._free(symbolsAddress);
            return result;
          }
          get nextSibling() {
            marshalNode(this);
            C._ts_node_next_sibling_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get previousSibling() {
            marshalNode(this);
            C._ts_node_prev_sibling_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get nextNamedSibling() {
            marshalNode(this);
            C._ts_node_next_named_sibling_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get previousNamedSibling() {
            marshalNode(this);
            C._ts_node_prev_named_sibling_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get descendantCount() {
            marshalNode(this);
            return C._ts_node_descendant_count_wasm(this.tree[0]);
          }
          get parent() {
            marshalNode(this);
            C._ts_node_parent_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          descendantForIndex(start, end = start) {
            if (typeof start !== "number" || typeof end !== "number") {
              throw new Error("Arguments must be numbers");
            }
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            setValue(address, start, "i32");
            setValue(address + SIZE_OF_INT, end, "i32");
            C._ts_node_descendant_for_index_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          namedDescendantForIndex(start, end = start) {
            if (typeof start !== "number" || typeof end !== "number") {
              throw new Error("Arguments must be numbers");
            }
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            setValue(address, start, "i32");
            setValue(address + SIZE_OF_INT, end, "i32");
            C._ts_node_named_descendant_for_index_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          descendantForPosition(start, end = start) {
            if (!isPoint(start) || !isPoint(end)) {
              throw new Error("Arguments must be {row, column} objects");
            }
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            marshalPoint(address, start);
            marshalPoint(address + SIZE_OF_POINT, end);
            C._ts_node_descendant_for_position_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          namedDescendantForPosition(start, end = start) {
            if (!isPoint(start) || !isPoint(end)) {
              throw new Error("Arguments must be {row, column} objects");
            }
            marshalNode(this);
            const address = TRANSFER_BUFFER + SIZE_OF_NODE;
            marshalPoint(address, start);
            marshalPoint(address + SIZE_OF_POINT, end);
            C._ts_node_named_descendant_for_position_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          walk() {
            marshalNode(this);
            C._ts_tree_cursor_new_wasm(this.tree[0]);
            return new TreeCursor(INTERNAL, this.tree);
          }
          toString() {
            marshalNode(this);
            const address = C._ts_node_to_string_wasm(this.tree[0]);
            const result = AsciiToString(address);
            C._free(address);
            return result;
          }
        }
        class TreeCursor {
          constructor(internal, tree) {
            assertInternal(internal);
            this.tree = tree;
            unmarshalTreeCursor(this);
          }
          delete() {
            marshalTreeCursor(this);
            C._ts_tree_cursor_delete_wasm(this.tree[0]);
            this[0] = this[1] = this[2] = 0;
          }
          reset(node) {
            marshalNode(node);
            marshalTreeCursor(this, TRANSFER_BUFFER + SIZE_OF_NODE);
            C._ts_tree_cursor_reset_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
          }
          resetTo(cursor) {
            marshalTreeCursor(this, TRANSFER_BUFFER);
            marshalTreeCursor(cursor, TRANSFER_BUFFER + SIZE_OF_CURSOR);
            C._ts_tree_cursor_reset_to_wasm(this.tree[0], cursor.tree[0]);
            unmarshalTreeCursor(this);
          }
          get nodeType() {
            return this.tree.language.types[this.nodeTypeId] || "ERROR";
          }
          get nodeTypeId() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_node_type_id_wasm(this.tree[0]);
          }
          get nodeStateId() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_node_state_id_wasm(this.tree[0]);
          }
          get nodeId() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_node_id_wasm(this.tree[0]);
          }
          get nodeIsNamed() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_node_is_named_wasm(this.tree[0]) === 1;
          }
          get nodeIsMissing() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_node_is_missing_wasm(this.tree[0]) === 1;
          }
          get nodeText() {
            marshalTreeCursor(this);
            const startIndex = C._ts_tree_cursor_start_index_wasm(this.tree[0]);
            const endIndex = C._ts_tree_cursor_end_index_wasm(this.tree[0]);
            return getText(this.tree, startIndex, endIndex);
          }
          get startPosition() {
            marshalTreeCursor(this);
            C._ts_tree_cursor_start_position_wasm(this.tree[0]);
            return unmarshalPoint(TRANSFER_BUFFER);
          }
          get endPosition() {
            marshalTreeCursor(this);
            C._ts_tree_cursor_end_position_wasm(this.tree[0]);
            return unmarshalPoint(TRANSFER_BUFFER);
          }
          get startIndex() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_start_index_wasm(this.tree[0]);
          }
          get endIndex() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_end_index_wasm(this.tree[0]);
          }
          get currentNode() {
            marshalTreeCursor(this);
            C._ts_tree_cursor_current_node_wasm(this.tree[0]);
            return unmarshalNode(this.tree);
          }
          get currentFieldId() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_field_id_wasm(this.tree[0]);
          }
          get currentFieldName() {
            return this.tree.language.fields[this.currentFieldId];
          }
          get currentDepth() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_depth_wasm(this.tree[0]);
          }
          get currentDescendantIndex() {
            marshalTreeCursor(this);
            return C._ts_tree_cursor_current_descendant_index_wasm(this.tree[0]);
          }
          gotoFirstChild() {
            marshalTreeCursor(this);
            const result = C._ts_tree_cursor_goto_first_child_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoLastChild() {
            marshalTreeCursor(this);
            const result = C._ts_tree_cursor_goto_last_child_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoFirstChildForIndex(goalIndex) {
            marshalTreeCursor(this);
            setValue(TRANSFER_BUFFER + SIZE_OF_CURSOR, goalIndex, "i32");
            const result = C._ts_tree_cursor_goto_first_child_for_index_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoFirstChildForPosition(goalPosition) {
            marshalTreeCursor(this);
            marshalPoint(TRANSFER_BUFFER + SIZE_OF_CURSOR, goalPosition);
            const result = C._ts_tree_cursor_goto_first_child_for_position_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoNextSibling() {
            marshalTreeCursor(this);
            const result = C._ts_tree_cursor_goto_next_sibling_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoPreviousSibling() {
            marshalTreeCursor(this);
            const result = C._ts_tree_cursor_goto_previous_sibling_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
          gotoDescendant(goalDescendantindex) {
            marshalTreeCursor(this);
            C._ts_tree_cursor_goto_descendant_wasm(this.tree[0], goalDescendantindex);
            unmarshalTreeCursor(this);
          }
          gotoParent() {
            marshalTreeCursor(this);
            const result = C._ts_tree_cursor_goto_parent_wasm(this.tree[0]);
            unmarshalTreeCursor(this);
            return result === 1;
          }
        }
        class Language {
          constructor(internal, address) {
            assertInternal(internal);
            this[0] = address;
            this.types = new Array(C._ts_language_symbol_count(this[0]));
            for (let i = 0, n = this.types.length; i < n; i++) {
              if (C._ts_language_symbol_type(this[0], i) < 2) {
                this.types[i] = UTF8ToString(C._ts_language_symbol_name(this[0], i));
              }
            }
            this.fields = new Array(C._ts_language_field_count(this[0]) + 1);
            for (let i = 0, n = this.fields.length; i < n; i++) {
              const fieldName = C._ts_language_field_name_for_id(this[0], i);
              if (fieldName !== 0) {
                this.fields[i] = UTF8ToString(fieldName);
              } else {
                this.fields[i] = null;
              }
            }
          }
          get version() {
            return C._ts_language_version(this[0]);
          }
          get fieldCount() {
            return this.fields.length - 1;
          }
          get stateCount() {
            return C._ts_language_state_count(this[0]);
          }
          fieldIdForName(fieldName) {
            const result = this.fields.indexOf(fieldName);
            if (result !== -1) {
              return result;
            } else {
              return null;
            }
          }
          fieldNameForId(fieldId) {
            return this.fields[fieldId] || null;
          }
          idForNodeType(type, named) {
            const typeLength = lengthBytesUTF8(type);
            const typeAddress = C._malloc(typeLength + 1);
            stringToUTF8(type, typeAddress, typeLength + 1);
            const result = C._ts_language_symbol_for_name(this[0], typeAddress, typeLength, named);
            C._free(typeAddress);
            return result || null;
          }
          get nodeTypeCount() {
            return C._ts_language_symbol_count(this[0]);
          }
          nodeTypeForId(typeId) {
            const name = C._ts_language_symbol_name(this[0], typeId);
            return name ? UTF8ToString(name) : null;
          }
          nodeTypeIsNamed(typeId) {
            return C._ts_language_type_is_named_wasm(this[0], typeId) ? true : false;
          }
          nodeTypeIsVisible(typeId) {
            return C._ts_language_type_is_visible_wasm(this[0], typeId) ? true : false;
          }
          nextState(stateId, typeId) {
            return C._ts_language_next_state(this[0], stateId, typeId);
          }
          lookaheadIterator(stateId) {
            const address = C._ts_lookahead_iterator_new(this[0], stateId);
            if (address) return new LookaheadIterable(INTERNAL, address, this);
            return null;
          }
          query(source) {
            const sourceLength = lengthBytesUTF8(source);
            const sourceAddress = C._malloc(sourceLength + 1);
            stringToUTF8(source, sourceAddress, sourceLength + 1);
            const address = C._ts_query_new(this[0], sourceAddress, sourceLength, TRANSFER_BUFFER, TRANSFER_BUFFER + SIZE_OF_INT);
            if (!address) {
              const errorId = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
              const errorByte = getValue(TRANSFER_BUFFER, "i32");
              const errorIndex = UTF8ToString(sourceAddress, errorByte).length;
              const suffix = source.substr(errorIndex, 100).split("\n")[0];
              let word = suffix.match(QUERY_WORD_REGEX)[0];
              let error;
              switch (errorId) {
               case 2:
                error = new RangeError(`Bad node name '${word}'`);
                break;

               case 3:
                error = new RangeError(`Bad field name '${word}'`);
                break;

               case 4:
                error = new RangeError(`Bad capture name @${word}`);
                break;

               case 5:
                error = new TypeError(`Bad pattern structure at offset ${errorIndex}: '${suffix}'...`);
                word = "";
                break;

               default:
                error = new SyntaxError(`Bad syntax at offset ${errorIndex}: '${suffix}'...`);
                word = "";
                break;
              }
              error.index = errorIndex;
              error.length = word.length;
              C._free(sourceAddress);
              throw error;
            }
            const stringCount = C._ts_query_string_count(address);
            const captureCount = C._ts_query_capture_count(address);
            const patternCount = C._ts_query_pattern_count(address);
            const captureNames = new Array(captureCount);
            const stringValues = new Array(stringCount);
            for (let i = 0; i < captureCount; i++) {
              const nameAddress = C._ts_query_capture_name_for_id(address, i, TRANSFER_BUFFER);
              const nameLength = getValue(TRANSFER_BUFFER, "i32");
              captureNames[i] = UTF8ToString(nameAddress, nameLength);
            }
            for (let i = 0; i < stringCount; i++) {
              const valueAddress = C._ts_query_string_value_for_id(address, i, TRANSFER_BUFFER);
              const nameLength = getValue(TRANSFER_BUFFER, "i32");
              stringValues[i] = UTF8ToString(valueAddress, nameLength);
            }
            const setProperties = new Array(patternCount);
            const assertedProperties = new Array(patternCount);
            const refutedProperties = new Array(patternCount);
            const predicates = new Array(patternCount);
            const textPredicates = new Array(patternCount);
            for (let i = 0; i < patternCount; i++) {
              const predicatesAddress = C._ts_query_predicates_for_pattern(address, i, TRANSFER_BUFFER);
              const stepCount = getValue(TRANSFER_BUFFER, "i32");
              predicates[i] = [];
              textPredicates[i] = [];
              const steps = [];
              let stepAddress = predicatesAddress;
              for (let j = 0; j < stepCount; j++) {
                const stepType = getValue(stepAddress, "i32");
                stepAddress += SIZE_OF_INT;
                const stepValueId = getValue(stepAddress, "i32");
                stepAddress += SIZE_OF_INT;
                if (stepType === PREDICATE_STEP_TYPE_CAPTURE) {
                  steps.push({
                    type: "capture",
                    name: captureNames[stepValueId]
                  });
                } else if (stepType === PREDICATE_STEP_TYPE_STRING) {
                  steps.push({
                    type: "string",
                    value: stringValues[stepValueId]
                  });
                } else if (steps.length > 0) {
                  if (steps[0].type !== "string") {
                    throw new Error("Predicates must begin with a literal value");
                  }
                  const operator = steps[0].value;
                  let isPositive = true;
                  let matchAll = true;
                  let captureName;
                  switch (operator) {
                   case "any-not-eq?":
                   case "not-eq?":
                    isPositive = false;

                   case "any-eq?":
                   case "eq?":
                    if (steps.length !== 3) {
                      throw new Error(`Wrong number of arguments to \`#${operator}\` predicate. Expected 2, got ${steps.length - 1}`);
                    }
                    if (steps[1].type !== "capture") {
                      throw new Error(`First argument of \`#${operator}\` predicate must be a capture. Got "${steps[1].value}"`);
                    }
                    matchAll = !operator.startsWith("any-");
                    if (steps[2].type === "capture") {
                      const captureName1 = steps[1].name;
                      const captureName2 = steps[2].name;
                      textPredicates[i].push(captures => {
                        const nodes1 = [];
                        const nodes2 = [];
                        for (const c of captures) {
                          if (c.name === captureName1) nodes1.push(c.node);
                          if (c.name === captureName2) nodes2.push(c.node);
                        }
                        const compare = (n1, n2, positive) => positive ? n1.text === n2.text : n1.text !== n2.text;
                        return matchAll ? nodes1.every(n1 => nodes2.some(n2 => compare(n1, n2, isPositive))) : nodes1.some(n1 => nodes2.some(n2 => compare(n1, n2, isPositive)));
                      });
                    } else {
                      captureName = steps[1].name;
                      const stringValue = steps[2].value;
                      const matches = n => n.text === stringValue;
                      const doesNotMatch = n => n.text !== stringValue;
                      textPredicates[i].push(captures => {
                        const nodes = [];
                        for (const c of captures) {
                          if (c.name === captureName) nodes.push(c.node);
                        }
                        const test = isPositive ? matches : doesNotMatch;
                        return matchAll ? nodes.every(test) : nodes.some(test);
                      });
                    }
                    break;

                   case "any-not-match?":
                   case "not-match?":
                    isPositive = false;

                   case "any-match?":
                   case "match?":
                    if (steps.length !== 3) {
                      throw new Error(`Wrong number of arguments to \`#${operator}\` predicate. Expected 2, got ${steps.length - 1}.`);
                    }
                    if (steps[1].type !== "capture") {
                      throw new Error(`First argument of \`#${operator}\` predicate must be a capture. Got "${steps[1].value}".`);
                    }
                    if (steps[2].type !== "string") {
                      throw new Error(`Second argument of \`#${operator}\` predicate must be a string. Got @${steps[2].value}.`);
                    }
                    captureName = steps[1].name;
                    const regex = new RegExp(steps[2].value);
                    matchAll = !operator.startsWith("any-");
                    textPredicates[i].push(captures => {
                      const nodes = [];
                      for (const c of captures) {
                        if (c.name === captureName) nodes.push(c.node.text);
                      }
                      const test = (text, positive) => positive ? regex.test(text) : !regex.test(text);
                      if (nodes.length === 0) return !isPositive;
                      return matchAll ? nodes.every(text => test(text, isPositive)) : nodes.some(text => test(text, isPositive));
                    });
                    break;

                   case "set!":
                    if (steps.length < 2 || steps.length > 3) {
                      throw new Error(`Wrong number of arguments to \`#set!\` predicate. Expected 1 or 2. Got ${steps.length - 1}.`);
                    }
                    if (steps.some(s => s.type !== "string")) {
                      throw new Error(`Arguments to \`#set!\` predicate must be a strings.".`);
                    }
                    if (!setProperties[i]) setProperties[i] = {};
                    setProperties[i][steps[1].value] = steps[2] ? steps[2].value : null;
                    break;

                   case "is?":
                   case "is-not?":
                    if (steps.length < 2 || steps.length > 3) {
                      throw new Error(`Wrong number of arguments to \`#${operator}\` predicate. Expected 1 or 2. Got ${steps.length - 1}.`);
                    }
                    if (steps.some(s => s.type !== "string")) {
                      throw new Error(`Arguments to \`#${operator}\` predicate must be a strings.".`);
                    }
                    const properties = operator === "is?" ? assertedProperties : refutedProperties;
                    if (!properties[i]) properties[i] = {};
                    properties[i][steps[1].value] = steps[2] ? steps[2].value : null;
                    break;

                   case "not-any-of?":
                    isPositive = false;

                   case "any-of?":
                    if (steps.length < 2) {
                      throw new Error(`Wrong number of arguments to \`#${operator}\` predicate. Expected at least 1. Got ${steps.length - 1}.`);
                    }
                    if (steps[1].type !== "capture") {
                      throw new Error(`First argument of \`#${operator}\` predicate must be a capture. Got "${steps[1].value}".`);
                    }
                    for (let i = 2; i < steps.length; i++) {
                      if (steps[i].type !== "string") {
                        throw new Error(`Arguments to \`#${operator}\` predicate must be a strings.".`);
                      }
                    }
                    captureName = steps[1].name;
                    const values = steps.slice(2).map(s => s.value);
                    textPredicates[i].push(captures => {
                      const nodes = [];
                      for (const c of captures) {
                        if (c.name === captureName) nodes.push(c.node.text);
                      }
                      if (nodes.length === 0) return !isPositive;
                      return nodes.every(text => values.includes(text)) === isPositive;
                    });
                    break;

                   default:
                    predicates[i].push({
                      operator: operator,
                      operands: steps.slice(1)
                    });
                  }
                  steps.length = 0;
                }
              }
              Object.freeze(setProperties[i]);
              Object.freeze(assertedProperties[i]);
              Object.freeze(refutedProperties[i]);
            }
            C._free(sourceAddress);
            return new Query(INTERNAL, address, captureNames, textPredicates, predicates, Object.freeze(setProperties), Object.freeze(assertedProperties), Object.freeze(refutedProperties));
          }
          static load(input) {
            let bytes;
            if (input instanceof Uint8Array) {
              bytes = Promise.resolve(input);
            } else {
              const url = input;
              if (typeof process !== "undefined" && process.versions && process.versions.node) {
                const fs = require("fs");
                bytes = Promise.resolve(fs.readFileSync(url));
              } else {
                bytes = fetch(url).then(response => response.arrayBuffer().then(buffer => {
                  if (response.ok) {
                    return new Uint8Array(buffer);
                  } else {
                    const body = new TextDecoder("utf-8").decode(buffer);
                    throw new Error(`Language.load failed with status ${response.status}.\n\n${body}`);
                  }
                }));
              }
            }
            return bytes.then(bytes => loadWebAssemblyModule(bytes, {
              loadAsync: true
            })).then(mod => {
              const symbolNames = Object.keys(mod);
              const functionName = symbolNames.find(key => LANGUAGE_FUNCTION_REGEX.test(key) && !key.includes("external_scanner_"));
              if (!functionName) {
                console.log(`Couldn't find language function in WASM file. Symbols:\n${JSON.stringify(symbolNames, null, 2)}`);
              }
              const languageAddress = mod[functionName]();
              return new Language(INTERNAL, languageAddress);
            });
          }
        }
        class LookaheadIterable {
          constructor(internal, address, language) {
            assertInternal(internal);
            this[0] = address;
            this.language = language;
          }
          get currentTypeId() {
            return C._ts_lookahead_iterator_current_symbol(this[0]);
          }
          get currentType() {
            return this.language.types[this.currentTypeId] || "ERROR";
          }
          delete() {
            C._ts_lookahead_iterator_delete(this[0]);
            this[0] = 0;
          }
          resetState(stateId) {
            return C._ts_lookahead_iterator_reset_state(this[0], stateId);
          }
          reset(language, stateId) {
            if (C._ts_lookahead_iterator_reset(this[0], language[0], stateId)) {
              this.language = language;
              return true;
            }
            return false;
          }
          [Symbol.iterator]() {
            const self = this;
            return {
              next() {
                if (C._ts_lookahead_iterator_next(self[0])) {
                  return {
                    done: false,
                    value: self.currentType
                  };
                }
                return {
                  done: true,
                  value: ""
                };
              }
            };
          }
        }
        class Query {
          constructor(internal, address, captureNames, textPredicates, predicates, setProperties, assertedProperties, refutedProperties) {
            assertInternal(internal);
            this[0] = address;
            this.captureNames = captureNames;
            this.textPredicates = textPredicates;
            this.predicates = predicates;
            this.setProperties = setProperties;
            this.assertedProperties = assertedProperties;
            this.refutedProperties = refutedProperties;
            this.exceededMatchLimit = false;
          }
          delete() {
            C._ts_query_delete(this[0]);
            this[0] = 0;
          }
          matches(node, {startPosition: startPosition = ZERO_POINT, endPosition: endPosition = ZERO_POINT, startIndex: startIndex = 0, endIndex: endIndex = 0, matchLimit: matchLimit = 4294967295, maxStartDepth: maxStartDepth = 4294967295, timeoutMicros: timeoutMicros = 0} = {}) {
            if (typeof matchLimit !== "number") {
              throw new Error("Arguments must be numbers");
            }
            marshalNode(node);
            C._ts_query_matches_wasm(this[0], node.tree[0], startPosition.row, startPosition.column, endPosition.row, endPosition.column, startIndex, endIndex, matchLimit, maxStartDepth, timeoutMicros);
            const rawCount = getValue(TRANSFER_BUFFER, "i32");
            const startAddress = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const didExceedMatchLimit = getValue(TRANSFER_BUFFER + 2 * SIZE_OF_INT, "i32");
            const result = new Array(rawCount);
            this.exceededMatchLimit = Boolean(didExceedMatchLimit);
            let filteredCount = 0;
            let address = startAddress;
            for (let i = 0; i < rawCount; i++) {
              const pattern = getValue(address, "i32");
              address += SIZE_OF_INT;
              const captureCount = getValue(address, "i32");
              address += SIZE_OF_INT;
              const captures = new Array(captureCount);
              address = unmarshalCaptures(this, node.tree, address, captures);
              if (this.textPredicates[pattern].every(p => p(captures))) {
                result[filteredCount] = {
                  pattern: pattern,
                  captures: captures
                };
                const setProperties = this.setProperties[pattern];
                if (setProperties) result[filteredCount].setProperties = setProperties;
                const assertedProperties = this.assertedProperties[pattern];
                if (assertedProperties) result[filteredCount].assertedProperties = assertedProperties;
                const refutedProperties = this.refutedProperties[pattern];
                if (refutedProperties) result[filteredCount].refutedProperties = refutedProperties;
                filteredCount++;
              }
            }
            result.length = filteredCount;
            C._free(startAddress);
            return result;
          }
          captures(node, {startPosition: startPosition = ZERO_POINT, endPosition: endPosition = ZERO_POINT, startIndex: startIndex = 0, endIndex: endIndex = 0, matchLimit: matchLimit = 4294967295, maxStartDepth: maxStartDepth = 4294967295, timeoutMicros: timeoutMicros = 0} = {}) {
            if (typeof matchLimit !== "number") {
              throw new Error("Arguments must be numbers");
            }
            marshalNode(node);
            C._ts_query_captures_wasm(this[0], node.tree[0], startPosition.row, startPosition.column, endPosition.row, endPosition.column, startIndex, endIndex, matchLimit, maxStartDepth, timeoutMicros);
            const count = getValue(TRANSFER_BUFFER, "i32");
            const startAddress = getValue(TRANSFER_BUFFER + SIZE_OF_INT, "i32");
            const didExceedMatchLimit = getValue(TRANSFER_BUFFER + 2 * SIZE_OF_INT, "i32");
            const result = [];
            this.exceededMatchLimit = Boolean(didExceedMatchLimit);
            const captures = [];
            let address = startAddress;
            for (let i = 0; i < count; i++) {
              const pattern = getValue(address, "i32");
              address += SIZE_OF_INT;
              const captureCount = getValue(address, "i32");
              address += SIZE_OF_INT;
              const captureIndex = getValue(address, "i32");
              address += SIZE_OF_INT;
              captures.length = captureCount;
              address = unmarshalCaptures(this, node.tree, address, captures);
              if (this.textPredicates[pattern].every(p => p(captures))) {
                const capture = captures[captureIndex];
                const setProperties = this.setProperties[pattern];
                if (setProperties) capture.setProperties = setProperties;
                const assertedProperties = this.assertedProperties[pattern];
                if (assertedProperties) capture.assertedProperties = assertedProperties;
                const refutedProperties = this.refutedProperties[pattern];
                if (refutedProperties) capture.refutedProperties = refutedProperties;
                result.push(capture);
              }
            }
            C._free(startAddress);
            return result;
          }
          predicatesForPattern(patternIndex) {
            return this.predicates[patternIndex];
          }
          disableCapture(captureName) {
            const captureNameLength = lengthBytesUTF8(captureName);
            const captureNameAddress = C._malloc(captureNameLength + 1);
            stringToUTF8(captureName, captureNameAddress, captureNameLength + 1);
            C._ts_query_disable_capture(this[0], captureNameAddress, captureNameLength);
            C._free(captureNameAddress);
          }
          didExceedMatchLimit() {
            return this.exceededMatchLimit;
          }
        }
        function getText(tree, startIndex, endIndex) {
          const length = endIndex - startIndex;
          let result = tree.textCallback(startIndex, null, endIndex);
          startIndex += result.length;
          while (startIndex < endIndex) {
            const string = tree.textCallback(startIndex, null, endIndex);
            if (string && string.length > 0) {
              startIndex += string.length;
              result += string;
            } else {
              break;
            }
          }
          if (startIndex > endIndex) {
            result = result.slice(0, length);
          }
          return result;
        }
        function unmarshalCaptures(query, tree, address, result) {
          for (let i = 0, n = result.length; i < n; i++) {
            const captureIndex = getValue(address, "i32");
            address += SIZE_OF_INT;
            const node = unmarshalNode(tree, address);
            address += SIZE_OF_NODE;
            result[i] = {
              name: query.captureNames[captureIndex],
              node: node
            };
          }
          return address;
        }
        function assertInternal(x) {
          if (x !== INTERNAL) throw new Error("Illegal constructor");
        }
        function isPoint(point) {
          return (point && typeof point.row === "number" && typeof point.column === "number");
        }
        function marshalNode(node) {
          let address = TRANSFER_BUFFER;
          setValue(address, node.id, "i32");
          address += SIZE_OF_INT;
          setValue(address, node.startIndex, "i32");
          address += SIZE_OF_INT;
          setValue(address, node.startPosition.row, "i32");
          address += SIZE_OF_INT;
          setValue(address, node.startPosition.column, "i32");
          address += SIZE_OF_INT;
          setValue(address, node[0], "i32");
        }
        function unmarshalNode(tree, address = TRANSFER_BUFFER) {
          const id = getValue(address, "i32");
          address += SIZE_OF_INT;
          if (id === 0) return null;
          const index = getValue(address, "i32");
          address += SIZE_OF_INT;
          const row = getValue(address, "i32");
          address += SIZE_OF_INT;
          const column = getValue(address, "i32");
          address += SIZE_OF_INT;
          const other = getValue(address, "i32");
          const result = new Node(INTERNAL, tree);
          result.id = id;
          result.startIndex = index;
          result.startPosition = {
            row: row,
            column: column
          };
          result[0] = other;
          return result;
        }
        function marshalTreeCursor(cursor, address = TRANSFER_BUFFER) {
          setValue(address + 0 * SIZE_OF_INT, cursor[0], "i32");
          setValue(address + 1 * SIZE_OF_INT, cursor[1], "i32");
          setValue(address + 2 * SIZE_OF_INT, cursor[2], "i32");
          setValue(address + 3 * SIZE_OF_INT, cursor[3], "i32");
        }
        function unmarshalTreeCursor(cursor) {
          cursor[0] = getValue(TRANSFER_BUFFER + 0 * SIZE_OF_INT, "i32");
          cursor[1] = getValue(TRANSFER_BUFFER + 1 * SIZE_OF_INT, "i32");
          cursor[2] = getValue(TRANSFER_BUFFER + 2 * SIZE_OF_INT, "i32");
          cursor[3] = getValue(TRANSFER_BUFFER + 3 * SIZE_OF_INT, "i32");
        }
        function marshalPoint(address, point) {
          setValue(address, point.row, "i32");
          setValue(address + SIZE_OF_INT, point.column, "i32");
        }
        function unmarshalPoint(address) {
          const result = {
            row: getValue(address, "i32") >>> 0,
            column: getValue(address + SIZE_OF_INT, "i32") >>> 0
          };
          return result;
        }
        function marshalRange(address, range) {
          marshalPoint(address, range.startPosition);
          address += SIZE_OF_POINT;
          marshalPoint(address, range.endPosition);
          address += SIZE_OF_POINT;
          setValue(address, range.startIndex, "i32");
          address += SIZE_OF_INT;
          setValue(address, range.endIndex, "i32");
          address += SIZE_OF_INT;
        }
        function unmarshalRange(address) {
          const result = {};
          result.startPosition = unmarshalPoint(address);
          address += SIZE_OF_POINT;
          result.endPosition = unmarshalPoint(address);
          address += SIZE_OF_POINT;
          result.startIndex = getValue(address, "i32") >>> 0;
          address += SIZE_OF_INT;
          result.endIndex = getValue(address, "i32") >>> 0;
          return result;
        }
        function marshalEdit(edit) {
          let address = TRANSFER_BUFFER;
          marshalPoint(address, edit.startPosition);
          address += SIZE_OF_POINT;
          marshalPoint(address, edit.oldEndPosition);
          address += SIZE_OF_POINT;
          marshalPoint(address, edit.newEndPosition);
          address += SIZE_OF_POINT;
          setValue(address, edit.startIndex, "i32");
          address += SIZE_OF_INT;
          setValue(address, edit.oldEndIndex, "i32");
          address += SIZE_OF_INT;
          setValue(address, edit.newEndIndex, "i32");
          address += SIZE_OF_INT;
        }
        // end include: /src/lib/binding_web/binding.js
        // include: /src/lib/binding_web/suffix.js
        for (const name of Object.getOwnPropertyNames(ParserImpl.prototype)) {
          Object.defineProperty(Parser.prototype, name, {
            value: ParserImpl.prototype[name],
            enumerable: false,
            writable: false
          });
        }
        Parser.Language = Language;
        Module.onRuntimeInitialized = () => {
          ParserImpl.init();
          resolveInitPromise();
        };
      });
    }
  }
  return Parser;
}();

if (typeof exports === "object") {
  module.exports = TreeSitter;
}
await TreeSitter.init({
  locateFile(scriptName, scriptDirectory) {
    return "/assets/tree-sitter.wasm";
  }
});
const Parser = TreeSitter;

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
function dispatchCustomEvent(node, suffix, detail, options = {}) {
    const eventName = `${node.nodeName.toLocaleLowerCase()}:${suffix}`;
    node.dispatchEvent(new CustomEvent(eventName, {
        detail,
        bubbles: true,
        composed: true,
        ...options,
    }));
}

const QUERY = `
;; Literals
(number) @constant.numeric.integer
(boolean) @constant.builtin.boolean
(string) @string
(bytes) @string
(date) @constant.numeric.integer

;; Comments
(block_comment) @comment.block
(line_comment) @comment.line

;; Variables
(variable) @variable
(param) @variable.parameter

(predicate
  (nname) @function
)

(fact
  (nname) @function
)

;; Keywords
[
  "trusting"
  "check if"
  "check all"
  "reject if"
  "allow if"
  "deny if"
] @keyword

[
  "authority"
  "previous"
] @constant.builtin

[
  "<-"
] @keyword.operator

;; Punctuation
[ "," ] @punctuation.delimiter

[ "("
  ")"
  "["
  "]"
] @punctuation.bracket

[
  "/"
  "*"
  "+"
  "-"
  "&"
  "|"
  "^"
  ">" "<" "<=" ">=" "==" "!="
  "&&"
  "||"
] @operator`;
let BcDatalogEditor = class BcDatalogEditor extends s {
    constructor() {
        var _a;
        super();
        this.code = "";
        this.marks = [];
        this.readonly = false;
        this._tree = null;
        this._captures = [];
        this._parser = null;
        this._query = null;
        const codeChild = this.querySelector("code");
        if (codeChild !== null) {
            this.code = trimLines$1((_a = codeChild.textContent) !== null && _a !== void 0 ? _a : "");
        }
    }
    connectedCallback() {
        super.connectedCallback();
        console.log("connected callback");
        console.log("Parser initialized");
        console.log(Parser.Language);
        Parser.Language.load("/assets/tree-sitter-biscuit.wasm").then((BiscuitDatalog) => {
            console.log("Language initialized");
            const p = new Parser();
            p.setLanguage(BiscuitDatalog);
            this._query = BiscuitDatalog.query(QUERY);
            this._parser = p;
            this.handleInput(this.code);
        });
    }
    firstUpdated(values) {
        super.firstUpdated(values);
        // trigger syntax highlighting for code provided as props
        if (this.shadowRoot) {
            this.handleInput(this.shadowRoot.querySelector("#editing").value);
        }
    }
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    attributeChangedCallback(name, oldValue, newValue) {
        super.attributeChangedCallback(name, oldValue, newValue);
        if (name === "code" && oldValue != newValue) {
            this.handleInput(newValue);
        }
    }
    handleInput(value) {
        if (this._parser && this._query) {
            const code = value !== null && value !== void 0 ? value : "";
            const tree = this._parser.parse(code);
            const captures = this._query.captures(tree.rootNode);
            this._tree = tree;
            this._captures = captures;
            this.code = code;
        }
        this.syncScroll();
    }
    syncScroll() {
        if (this.shadowRoot) {
            const textarea = this.shadowRoot.querySelector("#editing");
            const highlighting = this.shadowRoot.querySelector("#highlighting");
            if (textarea && highlighting) {
                highlighting.scrollTop = textarea.scrollTop;
                highlighting.scrollLeft = textarea.scrollLeft;
            }
        }
    }
    render() {
        const rendered = this.renderText2(this.code, this._captures, this.marks);
        const rows = Math.max(this.code.split("\n").length, 1);
        return x `<div id="wrapper">
      <textarea
        id="editing"
        rows=${rows}
        readonly=${l(this.readonly ? "true" : undefined)}
        @input=${(e) => {
            var _a;
            const code = (_a = e.target) === null || _a === void 0 ? void 0 : _a.value;
            this.handleInput(code);
            dispatchCustomEvent(this, "update", { code });
        }}
        @keydown=${(e) => e.stopPropagation()}
        spellcheck="false"
        @scroll=${this.syncScroll}
      >
${this.code}</textarea
      >
      <pre
        id="highlighting"
        aria-hidden="true"
      ><code id="highlighting-content">${o(rendered)}</code></pre>
    </div>`;
    }
    renderText2(text, captures, marks) {
        // captures come from tree-sitter and are assumed to be properly nested
        // (if nested at all, they are mostly contiguous).
        // marks however are user provided and completely separate from TS. so even
        // if the user is not doing bad things, they still don't have access to the
        // TS tree and can't be asked to provide marks that don't nest correctly
        // with TS captures.
        var _a;
        // captures and marks are grouped by starting index. We know that an
        // opening tag will always be there at this position.
        const ranges = new Map();
        marks.concat(captures).forEach((c) => {
            var _a;
            if (c.node.startIndex >= c.node.endIndex)
                return;
            ranges.set(c.node.startIndex, ((_a = ranges.get(c.node.startIndex)) !== null && _a !== void 0 ? _a : []).concat(c));
        });
        let output = "";
        // active ranges, indexed by their end index
        const activeRanges = new Map();
        [...text].forEach((c, i) => {
            var _a, _b, _c, _d;
            // every range is encoded as a span element, so there is no need
            // to care about the order, we only need to close the correct amount
            // of spans
            ((_a = activeRanges.get(i)) !== null && _a !== void 0 ? _a : []).forEach(() => {
                output += "</span>";
            });
            activeRanges.delete(i);
            const openingNow = (_b = ranges.get(i)) !== null && _b !== void 0 ? _b : [];
            // position of the next range end. ranges created now can't go further
            const lastValidIndex = (_c = [...activeRanges.keys()].sort()[0]) !== null && _c !== void 0 ? _c : text.length;
            // list of the ranges created by splitting opening ranges at the next
            // enclosing range end
            const createdRanges = [];
            // for each of the new ranges, make sure they are properly included in
            // the active ranges (ie they end before the next closing tag).
            // If they are not, we will need to close them at the closest boundary,
            // and open them again. if the newly created range still intersects with
            // an already defined one, it will be handled at its starting index, so
            // we don't need to do further work here.
            const openingAdapted = openingNow.map((r) => {
                var _a;
                // since we're operating on shallow copies, mutating `r` directly
                // would mutate it everywhere else (including across) renders
                let adapted = r;
                if (r.node.endIndex > lastValidIndex) {
                    // clamp the opening range at the end of the enclosing one,
                    // and put the leftover in a new one
                    adapted = {
                        ...r,
                        node: {
                            startIndex: adapted.node.startIndex,
                            endIndex: lastValidIndex,
                        },
                    };
                    createdRanges.push({
                        name: r.name,
                        node: {
                            startIndex: lastValidIndex,
                            endIndex: r.node.endIndex,
                        },
                    });
                }
                // opening ranges are now part of the active set
                activeRanges.set(adapted.node.endIndex, ((_a = activeRanges.get(adapted.node.endIndex)) !== null && _a !== void 0 ? _a : []).concat(adapted));
                return adapted;
            });
            // insert the newly created ranges in the range map so future iterations
            // can pick them up
            ranges.set(lastValidIndex, ((_d = ranges.get(lastValidIndex)) !== null && _d !== void 0 ? _d : []).concat(createdRanges));
            // now that we have the final list of ranges starting at the current
            // position, we can sort them so that the widest ranges are opened first,
            // to let them contain smaller ranges
            openingAdapted
                .sort((a, b) => {
                return b.node.endIndex - a.node.endIndex;
            })
                .forEach((r) => {
                output += `<span class="${r.name.split(".").join(" ")}">`;
            });
            if (c === `\n`) {
                output += "<br>";
            }
            else if (c === `<`) {
                output += "&lt;";
            }
            else if (c === `>`) {
                output += "&gt;";
            }
            else if (c === ` `) {
                output += " "; // pre collapes / ignores leading spaces. an non-breaking
                // space will always take the correct amount of space
            }
            else {
                output += c;
            }
        });
        // handle ranges closing after the last char
        ((_a = activeRanges.get(text.length)) !== null && _a !== void 0 ? _a : []).forEach(() => {
            output += "</span>";
        });
        // pre hides empty final lines (or containing just a space or a tab):
        // this nbsp forces pre to display an empty line. this makes sure a new
        // line is displayed when typing enter at the end of the last line.
        // The final <br> tag might be inside one or several spans tags, so we
        // need to keep looking right until we find the last `</span>` tag, and
        // then inspect what's just before
        let offset = 0;
        while (output.slice(offset - 7, 7) == "</span>") {
            offset -= 7;
        }
        if (output.slice(offset - 4, 4) === "<br>") {
            output += " ";
        }
        return output;
    }
};
BcDatalogEditor.styles = i$3 `
    #wrapper {
      position: relative;
    }

    #editing,
    #highlighting {
      margin: 0;
      padding: 10px;
      border: 0;
      width: calc(100% - 22px);
      overflow: auto;
      white-space: nowrap;
    }

    #editing,
    #highlighting,
    #highlighting * {
      font-size: var(--editor-font, 13px);
      font-family: monospace;
      line-height: calc(var(--editor-font, 13px) * 1.3);
    }

    #highlighting {
      border: 1px solid black;
    }

    #editing {
      position: relative;
      resize: vertical;
      z-index: 1;
      min-height: 5em;

      white-space: pre;
      color: transparent;
      background: transparent;
      caret-color: var(--foreground); /* Or choose your favorite color */
    }

    #highlighting {
      position: absolute;
      top: 0;
      bottom: 3px;
      left: 0;
      z-index: 0;
      color: var(--foreground);
      background-color: var(--background);
    }

    :host {
      --background: #fafafa;
      --foreground: #5c6166;
      --black: #e7eaed;
      --white: #fcfcfc;
      --blue: #399ee6;
      --light_blue: #55b4d4;
      --cyan: #478acc;
      --dark_gray: #d8d8d7;
      --gray: #828c9a;
      --green: #86b300;
      --green-bg: rgba(134, 170, 0, 0.3);
      --magenta: #a37acc;
      --orange: #fa8d3e;
      --red: #f07171;
      --red-bg: rgba(240, 113, 113, 0.3);
      --yellow: #ffaa33;
    }

    .constant {
      color: var(--magenta);
    }

    .string {
      color: var(--green);
    }

    .comment {
      color: var(--gray);
      font-style: italic;
    }

    .variable {
      color: var(--foreground);
    }

    .punctuation {
      color: var(--foreground);
    }

    .keyword {
      color: var(--orange);
    }

    .operator {
      color: var(--orange);
    }

    .function {
      color: var(--yellow);
    }

    .warning {
      color: var(--yellow);
    }

    .mark.failure {
      background-color: var(--red-bg);
    }

    .mark.error {
      text-decoration: underline wavy var(--red);
    }

    .mark.success {
      background-color: var(--green-bg);
    }

    .hint {
      color: var(--blue);
      font-weight: bold;
    }

    @media (prefers-color-scheme: dark) {
      :host {
        --background: #0f1419;
        --foreground: #bfbdb6;
        --black: #131721;
        --blue: #59c2ff;
        --dark_gray: #2d3640;
        --cyan: #73b8ff;
        --gray: #5c6773;
        --green: #aad94c;
        --green-bg: rgba(170, 217, 76, 0.5);
        --magenta: #d2a6ff;
        --orange: #ff8f40;
        --red: #f07178;
        --red-bg: rgba(240, 113, 120, 0.5);
        --yellow: #e6b450;
      }
      .comment {
        color: var(--gray);
        text-transform: italic;
      }
      .error {
        color: var(--red);
        font-weight: bold;
      }
      .hint {
        color: var(--blue);
        font-weight: bold;
      }
      .constant {
        color: var(--magenta);
      }
      .function {
        color: var(--yellow);
      }
      .keyword {
        color: var(--orange);
      }
      .operator {
        color: var(--orange);
      }
      .punctuation {
        color: var(--foreground);
      }
      .string {
        color: var(--green);
      }
      .variable {
        color: var(--foreground);
      }
      .warning {
        color: var(--yellow);
      }
    }
  `;
__decorate([
    n$1()
], BcDatalogEditor.prototype, "code", void 0);
__decorate([
    n$1()
], BcDatalogEditor.prototype, "marks", void 0);
__decorate([
    n$1()
], BcDatalogEditor.prototype, "readonly", void 0);
__decorate([
    t$1()
], BcDatalogEditor.prototype, "_tree", void 0);
__decorate([
    t$1()
], BcDatalogEditor.prototype, "_captures", void 0);
__decorate([
    t$1()
], BcDatalogEditor.prototype, "_parser", void 0);
__decorate([
    t$1()
], BcDatalogEditor.prototype, "_query", void 0);
BcDatalogEditor = __decorate([
    e$3("bc-datalog-editor")
], BcDatalogEditor);
function trimLines$1(str) {
    return str
        .trim()
        .split("\n")
        .map((line) => line.trim())
        .join("\n");
}

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcTokenEditor extends s {
    static get properties() {
        return {
            biscuit: { type: String },
            _blocks: { type: Array },
            parseErrors: { type: Array },
            markers: { type: Array },
        };
    }
    constructor() {
        super();
        this._blocks = [];
        for (const child of Array.from(this.children)) {
            this._blocks.push({ code: child.innerHTML });
        }
        this.parseErrors = [];
        this.markers = [];
    }
    _onAddBlock() {
        this._blocks = [...this._blocks, { code: "" }];
    }
    _onRemoveBlock(block) {
        this._blocks = this._blocks.filter((b) => b !== block);
    }
    _onUpdatedCode(block, code) {
        block.code = code;
        dispatchCustomEvent(this, "update", { blocks: this._blocks });
    }
    update(changedProperties) {
        super.update(changedProperties);
    }
    render() {
        return x `
      <div>
        <button @click=${this._onAddBlock}>add block</button>
      </div>
      ${this._blocks.map((block, index) => x `
          <button @click=${() => this._onRemoveBlock(block)}>
            remove this block
          </button>
          <bc-datalog-editor
            code=${block.code}
            .marks=${this.markers[index].concat(this.parseErrors[index])}
            @bc-datalog-editor:update="${(e) => {
            this._onUpdatedCode(block, e.detail.code);
        }}"
            }
          >
          </bc-datalog-editor>
        `)}
    `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-token-editor", BcTokenEditor);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcTokenContent extends s {
    static get properties() {
        return {
            content: { type: Object },
        };
    }
    constructor() {
        super();
    }
    render() {
        const content = this.content == null ? "no content yet" : this.content;
        return x ` <div><pre>${content}</pre></div> `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-token-content", BcTokenContent);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcAuthorizerEditor extends s {
    static get properties() {
        return {
            code: { type: String },
            parseErrors: { type: Array },
            markers: { type: Array },
        };
    }
    constructor() {
        super();
        if (this.children[0] != undefined) {
            this.code = this.children[0].innerHTML;
        }
        else {
            this.code = "";
        }
        this.parseErrors = [];
        this.markers = [];
    }
    _onUpdatedCode(code) {
        this.code = code;
        dispatchCustomEvent(this, "update", { code: code });
    }
    update(changedProperties) {
        super.update(changedProperties);
    }
    render() {
        return x `
      <bc-datalog-editor
        code=${this.code}
        .marks=${this.markers.concat(this.parseErrors)}
        @bc-datalog-editor:update="${(e) => {
            this._onUpdatedCode(e.detail.code);
        }}"
        }
      >
      </bc-datalog-editor>
    `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-authorizer-editor", BcAuthorizerEditor);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcAuthorizerContent extends s {
    static get properties() {
        return {
            content: { type: Array },
        };
    }
    constructor() {
        super();
        this.content = [];
    }
    render() {
        const deduped = [...new Set(this.content)];
        const sortedFacts = [...deduped].sort((f1, f2) => {
            if (f1.name == f2.name) {
                return f1.terms > f2.terms ? 1 : -1;
            }
            else {
                return f1.name > f2.name ? 1 : -1;
            }
        });
        var facts_map = {};
        var facts = "";
        var current_name;
        for (let fact of sortedFacts) {
            if (facts_map[fact.name] == undefined) {
                facts_map[fact.name] = [];
            }
            let alreadyThere = false;
            for (let terms of facts_map[fact.name]) {
                console.log(terms.join(), fact.terms.join());
                if (terms.join() === fact.terms.join()) {
                    alreadyThere = true;
                    break;
                }
            }
            if (alreadyThere)
                continue;
            facts_map[fact.name].push(fact.terms);
            if (current_name == undefined) {
                current_name = fact.name;
            }
            if (fact.name != current_name) {
                facts += "\n";
                current_name = fact.name;
            }
            facts += fact.name + "(" + fact.terms + ");\n";
        }
        return x ` <div>
      <bc-datalog-editor code=${facts} readonly="true"></bc-datalog-editor>
    </div>`;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-authorizer-content", BcAuthorizerContent);

function performance_now() {
  return performance.now();
}

var __wbg_star0 = /*#__PURE__*/Object.freeze({
    __proto__: null,
    performance_now: performance_now
});

let wasm;

function addToExternrefTable0(obj) {
    const idx = wasm.__externref_table_alloc();
    wasm.__wbindgen_export_2.set(idx, obj);
    return idx;
}

function handleError(f, args) {
    try {
        return f.apply(this, args);
    } catch (e) {
        const idx = addToExternrefTable0(e);
        wasm.__wbindgen_exn_store(idx);
    }
}

const cachedTextDecoder = (typeof TextDecoder !== 'undefined' ? new TextDecoder('utf-8', { ignoreBOM: true, fatal: true }) : { decode: () => { throw Error('TextDecoder not available') } } );

if (typeof TextDecoder !== 'undefined') { cachedTextDecoder.decode(); }
let cachedUint8ArrayMemory0 = null;

function getUint8ArrayMemory0() {
    if (cachedUint8ArrayMemory0 === null || cachedUint8ArrayMemory0.byteLength === 0) {
        cachedUint8ArrayMemory0 = new Uint8Array(wasm.memory.buffer);
    }
    return cachedUint8ArrayMemory0;
}

function getStringFromWasm0(ptr, len) {
    ptr = ptr >>> 0;
    return cachedTextDecoder.decode(getUint8ArrayMemory0().subarray(ptr, ptr + len));
}

let WASM_VECTOR_LEN = 0;

const cachedTextEncoder = (typeof TextEncoder !== 'undefined' ? new TextEncoder('utf-8') : { encode: () => { throw Error('TextEncoder not available') } } );

const encodeString = (typeof cachedTextEncoder.encodeInto === 'function'
    ? function (arg, view) {
    return cachedTextEncoder.encodeInto(arg, view);
}
    : function (arg, view) {
    const buf = cachedTextEncoder.encode(arg);
    view.set(buf);
    return {
        read: arg.length,
        written: buf.length
    };
});

function passStringToWasm0(arg, malloc, realloc) {

    if (realloc === undefined) {
        const buf = cachedTextEncoder.encode(arg);
        const ptr = malloc(buf.length, 1) >>> 0;
        getUint8ArrayMemory0().subarray(ptr, ptr + buf.length).set(buf);
        WASM_VECTOR_LEN = buf.length;
        return ptr;
    }

    let len = arg.length;
    let ptr = malloc(len, 1) >>> 0;

    const mem = getUint8ArrayMemory0();

    let offset = 0;

    for (; offset < len; offset++) {
        const code = arg.charCodeAt(offset);
        if (code > 0x7F) break;
        mem[ptr + offset] = code;
    }

    if (offset !== len) {
        if (offset !== 0) {
            arg = arg.slice(offset);
        }
        ptr = realloc(ptr, len, len = offset + arg.length * 3, 1) >>> 0;
        const view = getUint8ArrayMemory0().subarray(ptr + offset, ptr + len);
        const ret = encodeString(arg, view);

        offset += ret.written;
        ptr = realloc(ptr, len, offset, 1) >>> 0;
    }

    WASM_VECTOR_LEN = offset;
    return ptr;
}

let cachedDataViewMemory0 = null;

function getDataViewMemory0() {
    if (cachedDataViewMemory0 === null || cachedDataViewMemory0.buffer.detached === true || (cachedDataViewMemory0.buffer.detached === undefined && cachedDataViewMemory0.buffer !== wasm.memory.buffer)) {
        cachedDataViewMemory0 = new DataView(wasm.memory.buffer);
    }
    return cachedDataViewMemory0;
}

function isLikeNone(x) {
    return x === undefined || x === null;
}

function debugString(val) {
    // primitive types
    const type = typeof val;
    if (type == 'number' || type == 'boolean' || val == null) {
        return  `${val}`;
    }
    if (type == 'string') {
        return `"${val}"`;
    }
    if (type == 'symbol') {
        const description = val.description;
        if (description == null) {
            return 'Symbol';
        } else {
            return `Symbol(${description})`;
        }
    }
    if (type == 'function') {
        const name = val.name;
        if (typeof name == 'string' && name.length > 0) {
            return `Function(${name})`;
        } else {
            return 'Function';
        }
    }
    // objects
    if (Array.isArray(val)) {
        const length = val.length;
        let debug = '[';
        if (length > 0) {
            debug += debugString(val[0]);
        }
        for(let i = 1; i < length; i++) {
            debug += ', ' + debugString(val[i]);
        }
        debug += ']';
        return debug;
    }
    // Test for built-in
    const builtInMatches = /\[object ([^\]]+)\]/.exec(toString.call(val));
    let className;
    if (builtInMatches && builtInMatches.length > 1) {
        className = builtInMatches[1];
    } else {
        // Failed to match the standard '[object ClassName]'
        return toString.call(val);
    }
    if (className == 'Object') {
        // we're a user defined class or Object
        // JSON.stringify avoids problems with cycles, and is generally much
        // easier than looping through ownProperties of `val`.
        try {
            return 'Object(' + JSON.stringify(val) + ')';
        } catch (_) {
            return 'Object';
        }
    }
    // errors
    if (val instanceof Error) {
        return `${val.name}: ${val.message}\n${val.stack}`;
    }
    // TODO we could test for more things here, like `Set`s and `Map`s.
    return className;
}
/**
 * @param {any} query
 * @returns {any}
 */
function attenuate_token(query) {
    const ret = wasm.attenuate_token(query);
    return ret;
}

/**
 * @param {any} query
 * @returns {any}
 */
function execute(query) {
    const ret = wasm.execute(query);
    return ret;
}

/**
 * @param {any} query
 * @returns {any}
 */
function execute_serialized(query) {
    const ret = wasm.execute_serialized(query);
    return ret;
}

/**
 * @returns {any}
 */
function generate_keypair() {
    const ret = wasm.generate_keypair();
    return ret;
}

/**
 * @returns {any}
 */
function generate_ecdsa_keypair() {
    const ret = wasm.generate_ecdsa_keypair();
    return ret;
}

function takeFromExternrefTable0(idx) {
    const value = wasm.__wbindgen_export_2.get(idx);
    wasm.__externref_table_dealloc(idx);
    return value;
}
/**
 * @param {string} private_key
 * @returns {string}
 */
function get_public_key(private_key) {
    let deferred3_0;
    let deferred3_1;
    try {
        const ptr0 = passStringToWasm0(private_key, wasm.__wbindgen_malloc, wasm.__wbindgen_realloc);
        const len0 = WASM_VECTOR_LEN;
        const ret = wasm.get_public_key(ptr0, len0);
        var ptr2 = ret[0];
        var len2 = ret[1];
        if (ret[3]) {
            ptr2 = 0; len2 = 0;
            throw takeFromExternrefTable0(ret[2]);
        }
        deferred3_0 = ptr2;
        deferred3_1 = len2;
        return getStringFromWasm0(ptr2, len2);
    } finally {
        wasm.__wbindgen_free(deferred3_0, deferred3_1, 1);
    }
}

/**
 * @param {any} query
 * @returns {string}
 */
function generate_token(query) {
    let deferred2_0;
    let deferred2_1;
    try {
        const ret = wasm.generate_token(query);
        var ptr1 = ret[0];
        var len1 = ret[1];
        if (ret[3]) {
            ptr1 = 0; len1 = 0;
            throw takeFromExternrefTable0(ret[2]);
        }
        deferred2_0 = ptr1;
        deferred2_1 = len1;
        return getStringFromWasm0(ptr1, len1);
    } finally {
        wasm.__wbindgen_free(deferred2_0, deferred2_1, 1);
    }
}

/**
 * @param {any} query
 * @returns {any}
 */
function inspect_snapshot(query) {
    const ret = wasm.inspect_snapshot(query);
    return ret;
}

/**
 * @param {any} query
 * @returns {any}
 */
function parse_token(query) {
    const ret = wasm.parse_token(query);
    return ret;
}

async function __wbg_load(module, imports) {
    if (typeof Response === 'function' && module instanceof Response) {
        if (typeof WebAssembly.instantiateStreaming === 'function') {
            try {
                return await WebAssembly.instantiateStreaming(module, imports);

            } catch (e) {
                if (module.headers.get('Content-Type') != 'application/wasm') {
                    console.warn("`WebAssembly.instantiateStreaming` failed because your server does not serve Wasm with `application/wasm` MIME type. Falling back to `WebAssembly.instantiate` which is slower. Original error:\n", e);

                } else {
                    throw e;
                }
            }
        }

        const bytes = await module.arrayBuffer();
        return await WebAssembly.instantiate(bytes, imports);

    } else {
        const instance = await WebAssembly.instantiate(module, imports);

        if (instance instanceof WebAssembly.Instance) {
            return { instance, module };

        } else {
            return instance;
        }
    }
}

function __wbg_get_imports() {
    const imports = {};
    imports.wbg = {};
    imports.wbg.__wbg_buffer_609cc3eee51ed158 = function(arg0) {
        const ret = arg0.buffer;
        return ret;
    };
    imports.wbg.__wbg_call_672a4d21634d4a24 = function() { return handleError(function (arg0, arg1) {
        const ret = arg0.call(arg1);
        return ret;
    }, arguments) };
    imports.wbg.__wbg_call_7cccdd69e0791ae2 = function() { return handleError(function (arg0, arg1, arg2) {
        const ret = arg0.call(arg1, arg2);
        return ret;
    }, arguments) };
    imports.wbg.__wbg_crypto_574e78ad8b13b65f = function(arg0) {
        const ret = arg0.crypto;
        return ret;
    };
    imports.wbg.__wbg_debug_e17b51583ca6a632 = function(arg0, arg1, arg2, arg3) {
        console.debug(arg0, arg1, arg2, arg3);
    };
    imports.wbg.__wbg_done_769e5ede4b31c67b = function(arg0) {
        const ret = arg0.done;
        return ret;
    };
    imports.wbg.__wbg_error_524f506f44df1645 = function(arg0) {
        console.error(arg0);
    };
    imports.wbg.__wbg_error_7534b8e9a36f1ab4 = function(arg0, arg1) {
        let deferred0_0;
        let deferred0_1;
        try {
            deferred0_0 = arg0;
            deferred0_1 = arg1;
            console.error(getStringFromWasm0(arg0, arg1));
        } finally {
            wasm.__wbindgen_free(deferred0_0, deferred0_1, 1);
        }
    };
    imports.wbg.__wbg_error_80de38b3f7cc3c3c = function(arg0, arg1, arg2, arg3) {
        console.error(arg0, arg1, arg2, arg3);
    };
    imports.wbg.__wbg_getRandomValues_b8f5dbd5f3995a9e = function() { return handleError(function (arg0, arg1) {
        arg0.getRandomValues(arg1);
    }, arguments) };
    imports.wbg.__wbg_get_67b2ba62fc30de12 = function() { return handleError(function (arg0, arg1) {
        const ret = Reflect.get(arg0, arg1);
        return ret;
    }, arguments) };
    imports.wbg.__wbg_get_b9b93047fe3cf45b = function(arg0, arg1) {
        const ret = arg0[arg1 >>> 0];
        return ret;
    };
    imports.wbg.__wbg_getwithrefkey_bb8f74a92cb2e784 = function(arg0, arg1) {
        const ret = arg0[arg1];
        return ret;
    };
    imports.wbg.__wbg_info_033d8b8a0838f1d3 = function(arg0, arg1, arg2, arg3) {
        console.info(arg0, arg1, arg2, arg3);
    };
    imports.wbg.__wbg_instanceof_ArrayBuffer_e14585432e3737fc = function(arg0) {
        let result;
        try {
            result = arg0 instanceof ArrayBuffer;
        } catch (_) {
            result = false;
        }
        const ret = result;
        return ret;
    };
    imports.wbg.__wbg_instanceof_Uint8Array_17156bcf118086a9 = function(arg0) {
        let result;
        try {
            result = arg0 instanceof Uint8Array;
        } catch (_) {
            result = false;
        }
        const ret = result;
        return ret;
    };
    imports.wbg.__wbg_isArray_a1eab7e0d067391b = function(arg0) {
        const ret = Array.isArray(arg0);
        return ret;
    };
    imports.wbg.__wbg_iterator_9a24c88df860dc65 = function() {
        const ret = Symbol.iterator;
        return ret;
    };
    imports.wbg.__wbg_length_a446193dc22c12f8 = function(arg0) {
        const ret = arg0.length;
        return ret;
    };
    imports.wbg.__wbg_length_e2d2a49132c1b256 = function(arg0) {
        const ret = arg0.length;
        return ret;
    };
    imports.wbg.__wbg_log_1144c23b82f0ec1c = function(arg0, arg1) {
        console.log(getStringFromWasm0(arg0, arg1));
    };
    imports.wbg.__wbg_log_cad59bb680daec67 = function(arg0, arg1, arg2, arg3) {
        console.log(arg0, arg1, arg2, arg3);
    };
    imports.wbg.__wbg_msCrypto_a61aeb35a24c1329 = function(arg0) {
        const ret = arg0.msCrypto;
        return ret;
    };
    imports.wbg.__wbg_new_405e22f390576ce2 = function() {
        const ret = new Object();
        return ret;
    };
    imports.wbg.__wbg_new_78feb108b6472713 = function() {
        const ret = new Array();
        return ret;
    };
    imports.wbg.__wbg_new_8a6f238a6ece86ea = function() {
        const ret = new Error();
        return ret;
    };
    imports.wbg.__wbg_new_a12002a7f91c75be = function(arg0) {
        const ret = new Uint8Array(arg0);
        return ret;
    };
    imports.wbg.__wbg_newnoargs_105ed471475aaf50 = function(arg0, arg1) {
        const ret = new Function(getStringFromWasm0(arg0, arg1));
        return ret;
    };
    imports.wbg.__wbg_newwithbyteoffsetandlength_d97e637ebe145a9a = function(arg0, arg1, arg2) {
        const ret = new Uint8Array(arg0, arg1 >>> 0, arg2 >>> 0);
        return ret;
    };
    imports.wbg.__wbg_newwithlength_a381634e90c276d4 = function(arg0) {
        const ret = new Uint8Array(arg0 >>> 0);
        return ret;
    };
    imports.wbg.__wbg_next_25feadfc0913fea9 = function(arg0) {
        const ret = arg0.next;
        return ret;
    };
    imports.wbg.__wbg_next_6574e1a8a62d1055 = function() { return handleError(function (arg0) {
        const ret = arg0.next();
        return ret;
    }, arguments) };
    imports.wbg.__wbg_node_905d3e251edff8a2 = function(arg0) {
        const ret = arg0.node;
        return ret;
    };
    imports.wbg.__wbg_process_dc0fbacc7c1c06f7 = function(arg0) {
        const ret = arg0.process;
        return ret;
    };
    imports.wbg.__wbg_randomFillSync_ac0988aba3254290 = function() { return handleError(function (arg0, arg1) {
        arg0.randomFillSync(arg1);
    }, arguments) };
    imports.wbg.__wbg_require_60cc747a6bc5215a = function() { return handleError(function () {
        const ret = module.require;
        return ret;
    }, arguments) };
    imports.wbg.__wbg_set_37837023f3d740e8 = function(arg0, arg1, arg2) {
        arg0[arg1 >>> 0] = arg2;
    };
    imports.wbg.__wbg_set_3fda3bac07393de4 = function(arg0, arg1, arg2) {
        arg0[arg1] = arg2;
    };
    imports.wbg.__wbg_set_65595bdd868b3009 = function(arg0, arg1, arg2) {
        arg0.set(arg1, arg2 >>> 0);
    };
    imports.wbg.__wbg_stack_0ed75d68575b0f3c = function(arg0, arg1) {
        const ret = arg1.stack;
        const ptr1 = passStringToWasm0(ret, wasm.__wbindgen_malloc, wasm.__wbindgen_realloc);
        const len1 = WASM_VECTOR_LEN;
        getDataViewMemory0().setInt32(arg0 + 4 * 1, len1, true);
        getDataViewMemory0().setInt32(arg0 + 4 * 0, ptr1, true);
    };
    imports.wbg.__wbg_static_accessor_GLOBAL_88a902d13a557d07 = function() {
        const ret = typeof global === 'undefined' ? null : global;
        return isLikeNone(ret) ? 0 : addToExternrefTable0(ret);
    };
    imports.wbg.__wbg_static_accessor_GLOBAL_THIS_56578be7e9f832b0 = function() {
        const ret = typeof globalThis === 'undefined' ? null : globalThis;
        return isLikeNone(ret) ? 0 : addToExternrefTable0(ret);
    };
    imports.wbg.__wbg_static_accessor_SELF_37c5d418e4bf5819 = function() {
        const ret = typeof self === 'undefined' ? null : self;
        return isLikeNone(ret) ? 0 : addToExternrefTable0(ret);
    };
    imports.wbg.__wbg_static_accessor_WINDOW_5de37043a91a9c40 = function() {
        const ret = typeof window === 'undefined' ? null : window;
        return isLikeNone(ret) ? 0 : addToExternrefTable0(ret);
    };
    imports.wbg.__wbg_subarray_aa9065fa9dc5df96 = function(arg0, arg1, arg2) {
        const ret = arg0.subarray(arg1 >>> 0, arg2 >>> 0);
        return ret;
    };
    imports.wbg.__wbg_value_cd1ffa7b1ab794f1 = function(arg0) {
        const ret = arg0.value;
        return ret;
    };
    imports.wbg.__wbg_versions_c01dfd4722a88165 = function(arg0) {
        const ret = arg0.versions;
        return ret;
    };
    imports.wbg.__wbg_warn_aaf1f4664a035bd6 = function(arg0, arg1, arg2, arg3) {
        console.warn(arg0, arg1, arg2, arg3);
    };
    imports.wbg.__wbindgen_bigint_from_u128 = function(arg0, arg1) {
        const ret = BigInt.asUintN(64, arg0) << BigInt(64) | BigInt.asUintN(64, arg1);
        return ret;
    };
    imports.wbg.__wbindgen_bigint_from_u64 = function(arg0) {
        const ret = BigInt.asUintN(64, arg0);
        return ret;
    };
    imports.wbg.__wbindgen_boolean_get = function(arg0) {
        const v = arg0;
        const ret = typeof(v) === 'boolean' ? (v ? 1 : 0) : 2;
        return ret;
    };
    imports.wbg.__wbindgen_debug_string = function(arg0, arg1) {
        const ret = debugString(arg1);
        const ptr1 = passStringToWasm0(ret, wasm.__wbindgen_malloc, wasm.__wbindgen_realloc);
        const len1 = WASM_VECTOR_LEN;
        getDataViewMemory0().setInt32(arg0 + 4 * 1, len1, true);
        getDataViewMemory0().setInt32(arg0 + 4 * 0, ptr1, true);
    };
    imports.wbg.__wbindgen_error_new = function(arg0, arg1) {
        const ret = new Error(getStringFromWasm0(arg0, arg1));
        return ret;
    };
    imports.wbg.__wbindgen_in = function(arg0, arg1) {
        const ret = arg0 in arg1;
        return ret;
    };
    imports.wbg.__wbindgen_init_externref_table = function() {
        const table = wasm.__wbindgen_export_2;
        const offset = table.grow(4);
        table.set(0, undefined);
        table.set(offset + 0, undefined);
        table.set(offset + 1, null);
        table.set(offset + 2, true);
        table.set(offset + 3, false);
    };
    imports.wbg.__wbindgen_is_function = function(arg0) {
        const ret = typeof(arg0) === 'function';
        return ret;
    };
    imports.wbg.__wbindgen_is_object = function(arg0) {
        const val = arg0;
        const ret = typeof(val) === 'object' && val !== null;
        return ret;
    };
    imports.wbg.__wbindgen_is_string = function(arg0) {
        const ret = typeof(arg0) === 'string';
        return ret;
    };
    imports.wbg.__wbindgen_is_undefined = function(arg0) {
        const ret = arg0 === undefined;
        return ret;
    };
    imports.wbg.__wbindgen_jsval_loose_eq = function(arg0, arg1) {
        const ret = arg0 == arg1;
        return ret;
    };
    imports.wbg.__wbindgen_memory = function() {
        const ret = wasm.memory;
        return ret;
    };
    imports.wbg.__wbindgen_number_get = function(arg0, arg1) {
        const obj = arg1;
        const ret = typeof(obj) === 'number' ? obj : undefined;
        getDataViewMemory0().setFloat64(arg0 + 8 * 1, isLikeNone(ret) ? 0 : ret, true);
        getDataViewMemory0().setInt32(arg0 + 4 * 0, !isLikeNone(ret), true);
    };
    imports.wbg.__wbindgen_number_new = function(arg0) {
        const ret = arg0;
        return ret;
    };
    imports.wbg.__wbindgen_string_get = function(arg0, arg1) {
        const obj = arg1;
        const ret = typeof(obj) === 'string' ? obj : undefined;
        var ptr1 = isLikeNone(ret) ? 0 : passStringToWasm0(ret, wasm.__wbindgen_malloc, wasm.__wbindgen_realloc);
        var len1 = WASM_VECTOR_LEN;
        getDataViewMemory0().setInt32(arg0 + 4 * 1, len1, true);
        getDataViewMemory0().setInt32(arg0 + 4 * 0, ptr1, true);
    };
    imports.wbg.__wbindgen_string_new = function(arg0, arg1) {
        const ret = getStringFromWasm0(arg0, arg1);
        return ret;
    };
    imports.wbg.__wbindgen_throw = function(arg0, arg1) {
        throw new Error(getStringFromWasm0(arg0, arg1));
    };
    imports['./snippets/biscuit-auth-314ca57174ae0e6d/inline0.js'] = __wbg_star0;

    return imports;
}

function __wbg_finalize_init(instance, module) {
    wasm = instance.exports;
    __wbg_init.__wbindgen_wasm_module = module;
    cachedDataViewMemory0 = null;
    cachedUint8ArrayMemory0 = null;


    wasm.__wbindgen_start();
    return wasm;
}

async function __wbg_init(module_or_path) {
    if (wasm !== undefined) return wasm;


    if (typeof module_or_path !== 'undefined') {
        if (Object.getPrototypeOf(module_or_path) === Object.prototype) {
            ({module_or_path} = module_or_path);
        } else {
            console.warn('using deprecated parameters for the initialization function; pass a single object instead');
        }
    }

    if (typeof module_or_path === 'undefined') {
        module_or_path = new URL(new URL('assets/biscuit_bg-4cb62f78.wasm', import.meta.url).href, import.meta.url);
    }
    const imports = __wbg_get_imports();

    if (typeof module_or_path === 'string' || (typeof Request === 'function' && module_or_path instanceof Request) || (typeof URL === 'function' && module_or_path instanceof URL)) {
        module_or_path = fetch(module_or_path);
    }

    const { instance, module } = await __wbg_load(await module_or_path, imports);

    return __wbg_finalize_init(instance, module);
}

/**
 * TODO DOCS
 */
let BcAuthorizerResult = class BcAuthorizerResult extends s {
    renderLogicError(e) {
        var _a, _b, _c, _d;
        const failedChecksCount = (_d = (_b = (_a = e.Unauthorized) === null || _a === void 0 ? void 0 : _a.checks.length) !== null && _b !== void 0 ? _b : (_c = e.NoMatchingPolicy) === null || _c === void 0 ? void 0 : _c.checks.length) !== null && _d !== void 0 ? _d : 0;
        const failedChecks = failedChecksCount > 0
            ? x `<pre>${failedChecksCount} failed checks</pre>`
            : null;
        let policyError;
        if (e.NoMatchingPolicy) {
            policyError = x `<pre>No policy matched</pre>`;
        }
        else if (e.Unauthorized && e.Unauthorized.policy.Deny !== undefined) {
            policyError = x `<pre>A deny policy matched</pre>`;
        }
        return x `<div>${failedChecks} ${policyError}</div>`;
    }
    renderResult() {
        var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l, _m;
        const logicError = (_d = (_c = (_b = (_a = this.content) === null || _a === void 0 ? void 0 : _a.Ok) === null || _b === void 0 ? void 0 : _b.authorizer_result) === null || _c === void 0 ? void 0 : _c.Err) === null || _d === void 0 ? void 0 : _d.FailedLogic;
        const parsingError = (_f = (_e = this.content) === null || _e === void 0 ? void 0 : _e.Err) === null || _f === void 0 ? void 0 : _f.token;
        const publicKeyError = (_h = (_g = this.content) === null || _g === void 0 ? void 0 : _g.Err) === null || _h === void 0 ? void 0 : _h.root_key;
        const success = ((_l = (_k = (_j = this.content) === null || _j === void 0 ? void 0 : _j.Ok) === null || _k === void 0 ? void 0 : _k.authorizer_result) === null || _l === void 0 ? void 0 : _l.Ok) !== undefined;
        if (success) {
            return x `<div><pre>Success</pre></div>`;
        }
        else if (logicError) {
            return this.renderLogicError(logicError);
        }
        else if (publicKeyError) {
            return x `<div><pre>Public key parsing error</pre></div>`;
        }
        else if (parsingError) {
            return x `<div><pre>Token parsing error</pre></div>`;
        }
        else if ((_m = this.content) === null || _m === void 0 ? void 0 : _m.Err) {
            return x `<div><pre>Datalog execution error</pre></div>`;
        }
        else {
            console.error(this.content);
            return x `<div><pre>Unknown error</pre></div>`;
        }
    }
    render() {
        if (this.content === null) {
            return x ` <div><pre>no result yet</pre></div> `;
        }
        else {
            return this.renderResult();
        }
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
};
__decorate([
    n$1({ type: Object })
], BcAuthorizerResult.prototype, "content", void 0);
BcAuthorizerResult = __decorate([
    e$3("bc-authorizer-result")
], BcAuthorizerResult);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
let loadPromise = null;
async function initialize() {
    if (loadPromise == null) {
        console.log("will create wasm promise");
        loadPromise = __wbg_init();
    }
    console.log("returning wasm promise");
    return loadPromise;
}

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcAuthorizerExample extends s {
    static get properties() {
        return {
            code: { type: String },
            defaultAllow: { type: Boolean },
            started: { type: Boolean },
        };
    }
    constructor() {
        super();
        if (this.children[0] != undefined) {
            this.code = this.children[0].innerHTML;
        }
        else {
            this.code = "";
        }
        this.defaultAllow = false;
        this.started = false;
    }
    _onUpdatedCode(code) {
        this.code = code;
        dispatchCustomEvent(this, "update", { code: code });
    }
    firstUpdated(changedProperties) {
        initialize().then(() => (this.started = true));
    }
    update(changedProperties) {
        super.update(changedProperties);
    }
    render() {
        var _a, _b, _c, _d;
        var parseErrors = [];
        var markers = [];
        var authorizer_world = [];
        var code = this.code;
        if (this.defaultAllow) {
            code += "\n\nallow if true;";
        }
        if (this.started) {
            var state = {
                token_blocks: [""],
                authorizer_code: code,
                external_private_keys: [],
                query: "",
            };
            var result = execute(state);
            if (result.Ok === undefined) {
                for (let b of result.Err.blocks) {
                    var errors = [];
                    var marks = [];
                    for (let error of b) {
                        errors.push({
                            message: error.message,
                            severity: "error",
                            start: error.position.start,
                            end: error.position.end,
                        });
                    }
                    blockParseErrors.push(errors);
                }
                for (let error of result.Err.authorizer) {
                    parseErrors.push({
                        message: error.message,
                        severity: "error",
                        start: error.position.start,
                        end: error.position.end,
                    });
                }
            }
            else {
                for (let b of result.Ok.token_blocks) {
                    var marks = [];
                    for (let marker of b.markers) {
                        marks.push({
                            start: marker.position.start,
                            end: marker.position.end,
                            ok: marker.ok,
                        });
                    }
                    blockMarkers.push(marks);
                }
                for (let marker of result.Ok.authorizer_editor.markers) {
                    console.log(marker);
                    markers.push({
                        start: marker.position.start,
                        end: marker.position.end,
                        ok: marker.ok,
                    });
                }
            }
            authorizer_world = (_b = (_a = result.Ok) === null || _a === void 0 ? void 0 : _a.authorizer_world) !== null && _b !== void 0 ? _b : [];
            (_d = (_c = result.Ok) === null || _c === void 0 ? void 0 : _c.authorizer_result) !== null && _d !== void 0 ? _d : null;
            if (result.authorizer_editor != null) {
                for (let error of result.authorizer_editor.errors) {
                    parseErrors.push({
                        message: error.message,
                        severity: "error",
                        from: error.position.start,
                        to: error.position.end,
                    });
                }
                for (let marker of result.authorizer_editor.markers) {
                    console.log(marker);
                    markers.push({
                        start: marker.position.start,
                        end: marker.position.end,
                        ok: marker.ok,
                    });
                }
            }
        }
        return x `
      <bc-authorizer-editor
        code="${this.code}"
        parseErrors="${JSON.stringify(parseErrors)}"
        markers="${JSON.stringify(markers)}"
        @bc-authorizer-editor:update="${(e) => {
            this._onUpdatedCode(e.detail.code);
        }}"
        }
      >
      </bc-authorizer-editor>
      <em>Execution result</em>
      <bc-authorizer-result .content=${result}></bc-authorizer-result>
      <details>
        <summary>Facts</summary>
        <bc-authorizer-content
          content="${JSON.stringify(authorizer_world)}"
        ></bc-authorizer-content>
      </details>
    `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-authorizer-example", BcAuthorizerExample);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcDatalogExample extends s {
    static get properties() {
        return {
            code: { type: String },
            started: { type: Boolean },
        };
    }
    constructor() {
        super();
        if (this.children[0] != undefined) {
            this.code = this.children[0].innerHTML;
        }
        else {
            this.code = "";
        }
        console.log("constructor: got code:");
        console.log(this.code);
        this.started = false;
    }
    _onUpdatedCode(code) {
        this.code = code;
        dispatchCustomEvent(this, "update", { code: code });
    }
    firstUpdated(changedProperties) {
        initialize().then(() => (this.started = true));
    }
    update(changedProperties) {
        super.update(changedProperties);
    }
    render() {
        var parseErrors = [];
        var markers = [];
        var authorizer_world = [];
        var code = this.code;
        code += "\n\nallow if true;";
        if (this.started) {
            var state = {
                token_blocks: [],
                authorizer_code: code,
                query: "",
            };
            var result = execute(state);
            result.authorizer_result;
            authorizer_world = result.authorizer_world;
            if (result.authorizer_editor != null) {
                for (let error of result.authorizer_editor.errors) {
                    parseErrors.push({
                        message: error.message,
                        severity: "error",
                        start: error.position.start,
                        end: error.position.end,
                    });
                }
                for (let marker of result.authorizer_editor.markers) {
                    // do not display the marker for the additional "allow if true"
                    if (marker.position.start != this.code.length + 2) {
                        markers.push({
                            start: marker.position.start,
                            end: marker.position.end,
                            ok: marker.ok,
                        });
                    }
                }
            }
        }
        return x `
      <bc-authorizer-editor
        code="${this.code}"
        parseErrors="${JSON.stringify(parseErrors)}"
        markers="${JSON.stringify(markers)}"
        @bc-authorizer-editor:update="${(e) => {
            this._onUpdatedCode(e.detail.code);
        }}"
        }
      >
      </bc-authorizer-editor>
      Facts:
      <bc-authorizer-content
        content="${JSON.stringify(authorizer_world)}"
      ></bc-authorizer-content>
    `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: block;
        }
      `,
        ];
    }
}
window.customElements.define("bc-datalog-example", BcDatalogExample);

/*
 * SPDX-FileCopyrightText: 2021 Geoffroy Couprie <contact@geoffroycouprie.com>, Clément Delafargue <clement@delafargue.name>
 *
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * TODO DOCS
 */
class BcFullExample extends s {
    static get properties() {
        return {
            blocks: { type: Array },
            _authorizer: { type: String },
            _started: { type: Boolean },
        };
    }
    constructor() {
        super();
        this.blocks = [];
        for (let block of this.querySelectorAll(".block")) {
            console.log("block: " + block.innerHTML);
            this.blocks.push({ code: block.innerHTML });
        }
        let auth = this.querySelector(".authorizer");
        if (auth !== null) {
            this._authorizer = auth.innerHTML;
        }
        console.log("authorizer: " + this._authorizer);
        this._started = false;
    }
    _onUpdatedBlock(index, code) {
        console.log("full::_onUpdatedCode");
        console.log(code);
        this.blocks[index].code = code;
        console.log(this.blocks);
        dispatchCustomEvent(this, "update", { blocks: this.blocks });
        this.requestUpdate();
    }
    _onUpdatedAuthorizer(code) {
        this._authorizer = code;
        dispatchCustomEvent(this, "update", { _authorizer: code });
        this.requestUpdate();
    }
    firstUpdated(changedProperties) {
        initialize().then(() => {
            console.log("start");
            this._started = true;
        });
    }
    update(changedProperties) {
        super.update(changedProperties);
    }
    render() {
        var _a, _b, _c, _d;
        console.log("render0");
        if (!this._started) {
            return x ``;
        }
        console.log(this.blocks);
        let blocks = [];
        for (let b of this.blocks) {
            blocks.push(b.code);
        }
        var state = {
            token_blocks: blocks,
            authorizer_code: this._authorizer,
            query: "",
            external_private_keys: [],
        };
        console.log("WILL EXECUTE");
        var result = execute(state);
        console.log(result);
        var parseErrors = [];
        var markers = [];
        var authorizer_world = [];
        var blockParseErrors = [];
        var blockMarkers = [];
        // handle parse errors
        if (result.Ok === undefined) {
            for (let b of result.Err.blocks) {
                var errors = [];
                var marks = [];
                for (let error of b) {
                    errors.push({
                        message: error.message,
                        severity: "error",
                        start: error.position.start,
                        end: error.position.end,
                    });
                }
                blockParseErrors.push(errors);
            }
            for (let error of result.Err.authorizer) {
                parseErrors.push({
                    message: error.message,
                    severity: "error",
                    start: error.position.start,
                    end: error.position.end,
                });
            }
        }
        else {
            for (let b of result.Ok.token_blocks) {
                var marks = [];
                for (let marker of b.markers) {
                    marks.push({
                        start: marker.position.start,
                        end: marker.position.end,
                        ok: marker.ok,
                    });
                }
                blockMarkers.push(marks);
            }
            for (let marker of result.Ok.authorizer_editor.markers) {
                console.log(marker);
                markers.push({
                    start: marker.position.start,
                    end: marker.position.end,
                    ok: marker.ok,
                });
            }
        }
        authorizer_world = (_b = (_a = result.Ok) === null || _a === void 0 ? void 0 : _a.authorizer_world) !== null && _b !== void 0 ? _b : [];
        (_d = (_c = result.Ok) === null || _c === void 0 ? void 0 : _c.authorizer_result) !== null && _d !== void 0 ? _d : null;
        return x `
      <div class="blocks">
        <p>Token</p>
        ${this.blocks.map((block, index) => x `
            <p>Block ${index}</p>
            <bc-datalog-editor
              code=${block.code}
              .marks=${blockMarkers[index].concat(blockParseErrors[index])}
              @bc-datalog-editor:update="${(e) => {
            this._onUpdatedBlock(index, e.detail.code);
        }}"
              }
            >
            </bc-datalog-editor>
          `)}
      </div>

      <div class="authorizer">
        <p>Authorizer policies</p>
        <bc-authorizer-editor
          code="${this._authorizer}"
          parseErrors="${JSON.stringify(parseErrors)}"
          markers="${JSON.stringify(markers)}"
          @bc-authorizer-editor:update="${(e) => {
            this._onUpdatedAuthorizer(e.detail.code);
        }}"
          }
        >
        </bc-authorizer-editor>

        <em>Execution result</em>
        <bc-authorizer-result .content=${result}></bc-authorizer-result>
        <details>
          <summary>Facts</summary>
          <bc-authorizer-content
            content="${JSON.stringify(authorizer_world)}"
          ></bc-authorizer-content>
        </details>
      </div>
    `;
    }
    static get styles() {
        return [
            // language=CSS
            i$3 `
        :host {
          display: flex;
          flex-direction: column;
          text-align: left;
        }

        @media (prefers-color-scheme: dark) {
          :host {
            color: #dee2e6;
            background: #131314;
          }
          textarea {
            color: #dee2e6;
            background: #131314;
          }
        }

        @media (prefers-color-scheme: light) {
          :host {
            color: #1d2d35;
            background: #fff;
          }
        }

        @media (min-width: 576px) {
          :host {
            display: flex;
            flex-flow: row wrap;
            flex-direction: row;
          }

          .blocks {
            order: 1;
            width: 49%;
          }

          .authorizer {
            order: 2;
            width: 49%;
          }
        }

        .blocks {
          border: 1px rgba(128, 128, 128, 0.4) solid;
        }
        .authorizer {
          border-top: 1px rgba(128, 128, 128, 0.4) solid;
          border-right: 1px rgba(128, 128, 128, 0.4) solid;
          border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
        }

        p {
          margin-block-start: 0px;
          margin-block-end: 0px;
          padding: 0.2em;
          font-size: 0.8em;
          border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
        }

        bc-datalog-editor {
          border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
        }

        bc-authorizer-editor {
          border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
        }
      `,
        ];
    }
}
window.customElements.define("bc-full-example", BcFullExample);

const convertMarker = (marker) => {
    console.log({ marker });
    return {
        name: marker.ok ? "mark.success" : "mark.failure",
        node: {
            startIndex: marker.position.start,
            endIndex: marker.position.end,
        },
    };
};
const convertError = (error) => {
    console.log({ error });
    return {
        name: "mark.error",
        node: {
            startIndex: error.position.start,
            endIndex: error.position.end,
        },
    };
};
function trimLines(str) {
    return str
        .trim()
        .split("\n")
        .map((line) => line.trim())
        .join("\n");
}

/**
 * TODO DOCS
 */
let BCDatalogPlayground = class BCDatalogPlayground extends s {
    constructor() {
        var _a;
        super();
        this.code = "";
        this.showBlocks = false;
        this.blocks = [];
        this.started = false;
        const codeChild = this.querySelector(".authorizer");
        if (codeChild !== null) {
            this.code = trimLines((_a = codeChild.textContent) !== null && _a !== void 0 ? _a : "");
        }
        const blockChildren = this.querySelectorAll(".block");
        this.blocks = Array.from(blockChildren)
            .map((b, i) => {
            var _a;
            const code = trimLines((_a = b.textContent) !== null && _a !== void 0 ? _a : "");
            let externalKey = null;
            if (i > 0) {
                externalKey = b.getAttribute("privateKey");
            }
            return { code, externalKey };
        })
            .filter(({ code }, i) => i === 0 || code !== "");
    }
    firstUpdated() {
        initialize().then(() => (this.started = true));
    }
    addBlock() {
        const newBlocks = [...this.blocks];
        newBlocks.push({ code: "", externalKey: null });
        this.blocks = newBlocks;
    }
    onUpdatedBlock(blockId, e) {
        const newBlocks = [...this.blocks];
        newBlocks[blockId] = {
            code: e.detail.code,
            externalKey: newBlocks[blockId].externalKey,
        };
        this.blocks = newBlocks;
    }
    onUpdatedExternalKey(blockId, e) {
        const newBlocks = [...this.blocks];
        const newValue = e.target.value.trim();
        newBlocks[blockId] = {
            code: newBlocks[blockId].code,
            externalKey: newValue !== "" ? newValue : null,
        };
        this.blocks = newBlocks;
    }
    onUpdatedCode(e) {
        this.code = e.detail.code;
    }
    render() {
        var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k;
        let authorizer_world = [];
        let authorizer_result = null;
        const parseErrors = {
            blocks: [],
            authorizer: [],
        };
        const markers = {
            blocks: [],
            authorizer: [],
        };
        if (this.started) {
            const authorizerQuery = {
                token_blocks: this.blocks.length > 0 ? this.blocks.map(({ code }) => code) : [""],
                authorizer_code: this.code,
                query: "",
                external_private_keys: this.blocks.map(({ externalKey }) => externalKey),
            };
            const authorizerResult = execute(authorizerQuery);
            console.warn({ authorizerQuery, authorizerResult });
            authorizer_world = (_b = (_a = authorizerResult.Ok) === null || _a === void 0 ? void 0 : _a.authorizer_world) !== null && _b !== void 0 ? _b : [];
            authorizer_result = authorizerResult;
            markers.authorizer =
                (_d = (_c = authorizerResult.Ok) === null || _c === void 0 ? void 0 : _c.authorizer_editor.markers.map(convertMarker)) !== null && _d !== void 0 ? _d : [];
            parseErrors.authorizer =
                (_f = (_e = authorizerResult.Err) === null || _e === void 0 ? void 0 : _e.authorizer.map(convertError)) !== null && _f !== void 0 ? _f : [];
            markers.blocks =
                (_h = (_g = authorizerResult.Ok) === null || _g === void 0 ? void 0 : _g.token_blocks.map((b) => b.markers.map(convertMarker))) !== null && _h !== void 0 ? _h : [];
            parseErrors.blocks =
                (_k = (_j = authorizerResult.Err) === null || _j === void 0 ? void 0 : _j.blocks.map((b) => b.map(convertError))) !== null && _k !== void 0 ? _k : [];
        }
        return x `
      ${this.renderBlocks(markers.blocks, parseErrors.blocks)}
      ${this.renderAuthorizer(markers.authorizer, parseErrors.authorizer)}
      <p>Result</p>
      <bc-authorizer-result .content=${authorizer_result}>
      </bc-authorizer-result>
      <p>Facts</p>
      <bc-authorizer-content
        .content=${authorizer_world}
      ></bc-authorizer-content>
    `;
    }
    renderExternalKeyInput(blockId) {
        if (blockId <= 0)
            return;
        return x `
      <input
        @input=${(e) => this.onUpdatedExternalKey(blockId, e)}
        value=${this.blocks[blockId].externalKey}
      />
    `;
    }
    renderBlock(blockId, code, markers = [], errors = []) {
        return x ` <p>
        ${blockId == 0 ? "Authority block" : "Block " + blockId}:
        ${this.renderExternalKeyInput(blockId)}
      </p>
      <bc-datalog-editor
        code=${code}
        .marks=${markers.concat(errors)}
        @bc-datalog-editor:update=${(e) => this.onUpdatedBlock(blockId, e)}
        }
      >
      </bc-datalog-editor>`;
    }
    renderBlocks(markers, errors) {
        if (!this.showBlocks)
            return;
        return x `
      ${this.blocks.map(({ code }, id) => {
            return this.renderBlock(id, code, markers[id], errors[id]);
        })}
      <button @click=${this.addBlock}>Add block</button>
    `;
    }
    renderAuthorizer(markers, parseErrors) {
        return x ` <p>Authorizer</p>
      <bc-datalog-editor
        code=${this.code}
        @bc-datalog-editor:update=${this.onUpdatedCode}
        .marks=${markers.concat(parseErrors)}
      >
      </bc-datalog-editor>`;
    }
};
__decorate([
    n$1()
], BCDatalogPlayground.prototype, "code", void 0);
__decorate([
    n$1()
], BCDatalogPlayground.prototype, "showBlocks", void 0);
__decorate([
    t$1()
], BCDatalogPlayground.prototype, "blocks", void 0);
__decorate([
    t$1()
], BCDatalogPlayground.prototype, "started", void 0);
BCDatalogPlayground = __decorate([
    e$3("bc-datalog-playground")
], BCDatalogPlayground);

/**
 * TODO DOCS
 */
let BcTokenPrinter = class BcTokenPrinter extends s {
    constructor() {
        var _a;
        super();
        this.biscuit = "";
        this.readonly = false;
        this.showAuthorizer = false;
        this.authorizer = "";
        this.rootPublicKey = "";
        this.showAttenuation = false;
        this.extraBlocks = [];
        this._started = false;
        const authorizerChild = this.querySelector(".authorizer");
        if (authorizerChild !== null) {
            this.authorizer = trimLines((_a = authorizerChild.textContent) !== null && _a !== void 0 ? _a : "");
        }
    }
    firstUpdated() {
        initialize().then(() => (this._started = true));
    }
    _onUpdatedToken(e) {
        if (this.readonly)
            return;
        this.biscuit = e.target.value.trim();
    }
    _onUpdatedPublicKey(e) {
        this.rootPublicKey = e.target.value.trim();
    }
    _onUpdatedAuthorizer(e) {
        if (!this.showAuthorizer)
            return;
        this.authorizer = e.detail.code;
    }
    _onUpdatedExtraBlock(blockId, e) {
        const newBlocks = [...this.extraBlocks];
        newBlocks[blockId] = e.detail.code;
        this.extraBlocks = newBlocks;
    }
    _addExtraBlock() {
        const newBlocks = [...this.extraBlocks].filter((b) => b !== "");
        newBlocks.push("");
        this.extraBlocks = newBlocks;
    }
    renderTokenInput() {
        if (this.readonly) {
            return x `
        <div class="code">
          <p>Encoded token</p>
          <code>${this.biscuit}</code>
        </div>
      `;
        }
        return x `
      <div class="code">
        <p>Encoded token</p>
        <textarea @input=${this._onUpdatedToken}>${this.biscuit}</textarea>
      </div>
    `;
    }
    renderNotStarted() {
        return x `
      <div class="token">
        ${this.renderTokenInput()}
        <div class="content">loading biscuit token</div>
      </div>
    `;
    }
    renderEmptyToken() {
        return x `
      ${this.renderTokenInput()}
      <div class="content">Please enter a base64-encoded token</div>
    `;
    }
    renderResult(error, blocks, blockMarkers) {
        if (this.biscuit === "") {
            return x `
        ${this.renderTokenInput()}
        <div class="content">Please enter a base64-encoded token</div>
      `;
        }
        if (error) {
            return x `
        ${this.renderTokenInput()}
        <div class="content">${error}</div>
      `;
        }
        return x `
      ${this.renderTokenInput()}
      <div class="content">
        <p>Decoded token</p>
        ${blocks.map((block, index) => {
            var _a, _b;
            return x `
        <div>
        <p>${index === 0 ? "Authority block" : `Block ${index}`}:</p>
        <p class="revocation-id">Revocation id: <span class="id">${block.revocation_id}</span></p>
        <p class="external-key">Signed by: <span class="id">${(_a = block.external_key) !== null && _a !== void 0 ? _a : "n/a"}</span></p>
        <bc-datalog-editor
          code=${block.code}
          .marks=${(_b = blockMarkers[index]) !== null && _b !== void 0 ? _b : []}
          readonly="true"
        </bc-datalog-editor>
        </div>
      `;
        })}
      </div>
    `;
    }
    renderAuthorizer(result) {
        var _a, _b, _c, _d;
        if (!this.showAuthorizer)
            return;
        const markers = (_b = (_a = result.Ok) === null || _a === void 0 ? void 0 : _a.authorizer_editor.markers) !== null && _b !== void 0 ? _b : [];
        const errors = (_d = (_c = result.Err) === null || _c === void 0 ? void 0 : _c.authorizer) !== null && _d !== void 0 ? _d : [];
        return x `
      <div class="code">
        <p class="public-key-input">
          <label for="public-key">Public key</label>
          <input
            @input=${this._onUpdatedPublicKey}
            id="public-key"
            value="${this.rootPublicKey}"
          />
        </p>
        <p>Authorizer</p>
        <bc-authorizer-editor
          code="${this.authorizer}"
          .markers=${markers.map(convertMarker)}
          .parseErrors=${errors.map(convertError)}
          @bc-authorizer-editor:update=${this._onUpdatedAuthorizer}
        >
        </bc-authorizer-editor>
      </div>
      <div class="content">
        <p>Result</p>
        ${this.renderAuthorizerResult(result)}
      </div>
    `;
    }
    renderAuthorizerResult(result) {
        if (this.biscuit === "") {
            return x `<div><pre>Please enter a base64-encoded token</pre></div>`;
        }
        if (this.rootPublicKey === "") {
            return x `<div><pre>Please enter a public key</pre></div>`;
        }
        return x `<bc-authorizer-result
      .content=${result}
    ></bc-authorizer-result>`;
    }
    renderExtraBlock(blockId, errors = [], blocksOffset) {
        var _a;
        return x `
      <p>${"Block " + (blocksOffset + blockId)}:</p>
      <bc-datalog-editor
        code="${(_a = this.extraBlocks[blockId]) !== null && _a !== void 0 ? _a : ""}"
        .marks=${errors.map(convertError)}
        @bc-datalog-editor:update=${(e) => this._onUpdatedExtraBlock(blockId, e)}
      >
      </bc-datalog-editor>
    `;
    }
    renderAttenuation(blocksOffset) {
        var _a, _b, _c, _d;
        if (!this.showAttenuation)
            return;
        const result = attenuate_token({
            token: this.biscuit,
            blocks: this.extraBlocks.filter((b) => b !== ""),
        });
        const blocksWithErrors = [];
        ((_c = (_b = (_a = result.Err) === null || _a === void 0 ? void 0 : _a.BlockParseErrors) === null || _b === void 0 ? void 0 : _b.blocks) !== null && _c !== void 0 ? _c : []).forEach((errors, bId) => {
            if (errors.length > 0) {
                blocksWithErrors.push(bId);
            }
        });
        let errorMessage = "An error has happened";
        if (blocksWithErrors.length > 0) {
            const blockList = blocksWithErrors
                .map((bId) => (bId + blocksOffset).toString())
                .join(", ");
            errorMessage =
                "Please correct the datalog input on the following blocks: " +
                    blockList;
        }
        let attenuated;
        if (this.biscuit == "") {
            attenuated = x `Please enter a base64-encoded token`;
        }
        else {
            attenuated = (_d = result.Ok) !== null && _d !== void 0 ? _d : errorMessage;
        }
        return x `
      <div class="code">
        <p>Extra blocks</p>
        ${this.extraBlocks.map((_, id) => {
            var _a, _b, _c;
            const blockErrors = (_c = (_b = (_a = result.Err) === null || _a === void 0 ? void 0 : _a.BlockParseErrors) === null || _b === void 0 ? void 0 : _b.blocks[id]) !== null && _c !== void 0 ? _c : [];
            return this.renderExtraBlock(id, blockErrors, blocksOffset);
        })}
        <button @click=${this._addExtraBlock}>Add block</button>
      </div>
      <div class="content">
        <p>Attenuated token</p>
        <code>${attenuated}</code>
      </div>
    `;
    }
    render() {
        var _a, _b;
        if (!this._started)
            return this.renderNotStarted();
        const parseResult = parse_token({ data: this.biscuit });
        const blocks = parseResult.token_blocks.map((code, i) => ({
            code,
            revocation_id: parseResult.revocation_ids[i],
            external_key: parseResult.external_keys[i],
        }));
        const authorizerQuery = {
            token: this.biscuit,
            root_public_key: this.rootPublicKey,
            authorizer_code: this.authorizer,
            query: "",
        };
        const authorizerResult = execute_serialized(authorizerQuery);
        const blockMarkers = (_b = (_a = authorizerResult.Ok) === null || _a === void 0 ? void 0 : _a.token_blocks.map((b) => b.markers.map(convertMarker))) !== null && _b !== void 0 ? _b : [];
        // Extra blocks numbers are offset by the amount of blocks already in the token
        const blocksOffset = blocks.length;
        return x `
      <div class="row">
        ${this.renderResult(parseResult.error, blocks, this.showAuthorizer ? blockMarkers : [])}
      </div>
      <div class="row">${this.renderAuthorizer(authorizerResult)}</div>
      <div class="row">${this.renderAttenuation(blocksOffset)}</div>
    `;
    }
};
BcTokenPrinter.styles = i$3 `
    .row {
      display: flex;
      flex-direction: column;
      text-align: left;
    }

    .public-key-input {
      display: flex;
    }

    .public-key-input label {
      height: 1.5em;
    }

    #public-key {
      margin-left: 1em;
      flex-grow: 1;
    }

    @media (prefers-color-scheme: dark) {
      .row {
        color: #dee2e6;
        background: #131314;
      }
      textarea {
        color: #dee2e6;
        background: #131314;
      }
    }

    @media (prefers-color-scheme: light) {
      .row {
        color: #1d2d35;
        background: #fff;
      }
    }

    @media (min-width: 576px) {
      .row {
        display: flex;
        flex-flow: row wrap;
        flex-direction: row;
      }

      .code {
        order: 1;
        width: 49%;
      }

      .content {
        order: 2;
        width: 49%;
      }
    }

    code {
      overflow-wrap: anywhere;
      padding: 0.2em;
      padding-top: 1em;
    }

    .code {
      border: 1px rgba(128, 128, 128, 0.4) solid;
      display: flex;
      flex-direction: column;
    }

    textarea {
      flex-grow: 1;
      border: 0;
    }

    .content {
      border-top: 1px rgba(128, 128, 128, 0.4) solid;
      border-right: 1px rgba(128, 128, 128, 0.4) solid;
    }

    p {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
      margin-block-start: 0px;
      margin-block-end: 0px;
      padding: 0.2em;
      font-size: 0.8em;
    }

    .revocation-id,
    .external-key {
      overflow: hidden;
      diplay: inline-block;
      text-overflow: ellipsis;
      max-width: 100;
    }

    .revocation-id,
    .external-key > .id {
      user-select: all;
    }

    .content code {
      user-select: all;
    }

    bc-datalog-editor {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
    }
  `;
__decorate([
    n$1()
], BcTokenPrinter.prototype, "biscuit", void 0);
__decorate([
    n$1()
], BcTokenPrinter.prototype, "readonly", void 0);
__decorate([
    n$1()
], BcTokenPrinter.prototype, "showAuthorizer", void 0);
__decorate([
    n$1()
], BcTokenPrinter.prototype, "authorizer", void 0);
__decorate([
    n$1()
], BcTokenPrinter.prototype, "rootPublicKey", void 0);
__decorate([
    n$1()
], BcTokenPrinter.prototype, "showAttenuation", void 0);
__decorate([
    t$1()
], BcTokenPrinter.prototype, "extraBlocks", void 0);
__decorate([
    t$1()
], BcTokenPrinter.prototype, "_started", void 0);
BcTokenPrinter = __decorate([
    e$3("bc-token-printer")
], BcTokenPrinter);

/**
 * TODO DOCS
 */
let BcTokenGenerator = class BcTokenGenerator extends s {
    constructor() {
        super();
        this.privateKey = "";
        this._alg = "ed25519";
        this._blocks = [];
        this._started = false;
        console.log("constructor");
        const blockChildren = this.querySelectorAll(".block");
        console.log({ blockChildren });
        this._blocks = Array.from(blockChildren)
            .map((b, i) => {
            var _a;
            const code = trimLines((_a = b.textContent) !== null && _a !== void 0 ? _a : "");
            let externalKey = null;
            if (i > 0) {
                externalKey = b.getAttribute("privateKey");
            }
            return { code, externalKey };
        })
            .filter(({ code }, i) => i === 0 || code !== "");
        if (this._blocks.length === 0) {
            this._blocks = [{ code: "", externalKey: null }];
        }
    }
    firstUpdated() {
        initialize().then(() => (this._started = true));
    }
    _onUpdatedAlg(e) {
        var _a;
        const alg = (_a = e.target.selectedOptions[0]) === null || _a === void 0 ? void 0 : _a.value;
        if (alg) {
            this._alg = alg;
        }
    }
    _onUpdatedBlock(blockId, e) {
        const newBlocks = [...this._blocks];
        newBlocks[blockId] = {
            code: e.detail.code,
            externalKey: this._blocks[blockId].externalKey,
        };
        this._blocks = newBlocks;
    }
    _onUpdatedBlockKey(blockId, e) {
        const newBlocks = [...this._blocks];
        newBlocks[blockId] = {
            code: this._blocks[blockId].code,
            externalKey: e.target.value.trim(),
        };
        this._blocks = newBlocks;
    }
    generateKey() {
        if (this._alg == "secp256r1") {
            const { private_key } = generate_ecdsa_keypair();
            this.privateKey = private_key;
        }
        else {
            const { private_key } = generate_keypair();
            this.privateKey = private_key;
        }
    }
    addBlock() {
        const newBlocks = [...this._blocks];
        newBlocks.push({ code: "", externalKey: null });
        this._blocks = newBlocks;
    }
    renderNotStarted() {
        return x `
      <div class="row">
        <div class="content">loading biscuit generator</div>
      </div>
    `;
    }
    renderKeyInput() {
        const publicKey = this.privateKey && get_public_key(this.privateKey);
        return x `
      <div class="row">
        <p>
          <label for="private-key">Private key</label>
          <input id="private-key" value="${this.privateKey}" /><br />
          <label for="public-key">Public key</label>
          <input id="public-key" value="${publicKey}" readonly disabled /><br />
          <button @click=${this.generateKey}>Generate key</button>
          <select @change=${this._onUpdatedAlg}>
            <option value="ed25519">ed25519</option>
            <option value="secp256r1">secp256r1</option>
          </select>
        </p>
      </div>
    `;
    }
    renderBlockKeyInput(blockId) {
        if (blockId <= 0)
            return;
        return x `<input
      @input=${(e) => this._onUpdatedBlockKey(blockId, e)}
      placeholder="Third party private key"
    />`;
    }
    renderBlock(blockId, errors = []) {
        var _a, _b;
        return x `
      <p>${blockId == 0 ? "Authority block" : "Block " + blockId}:</p>
      ${this.renderBlockKeyInput(blockId)}
      <bc-datalog-editor
        code="${(_b = (_a = this._blocks[blockId]) === null || _a === void 0 ? void 0 : _a.code) !== null && _b !== void 0 ? _b : ""}"
        .marks=${errors.map(convertError)}
        @bc-datalog-editor:update=${(e) => this._onUpdatedBlock(blockId, e)}
      >
      </bc-datalog-editor>
    `;
    }
    render() {
        var _a, _b, _c, _d, _e, _f, _g, _h, _j;
        if (!this._started)
            return this.renderNotStarted();
        const nonEmptyBlocks = this._blocks.filter(({ code }) => code !== "");
        const query = {
            token_blocks: nonEmptyBlocks.map(({ code }) => code),
            private_key: this.privateKey,
            external_private_keys: nonEmptyBlocks.map(({ externalKey }) => externalKey),
        };
        let result;
        try {
            result = {
                Ok: generate_token(query),
            };
        }
        catch (error) {
            result = { Err: error };
        }
        const blocksWithErrors = [];
        ((_c = (_b = (_a = result.Err) === null || _a === void 0 ? void 0 : _a.Parse) === null || _b === void 0 ? void 0 : _b.blocks) !== null && _c !== void 0 ? _c : []).forEach((errors, bId) => {
            if (errors.length > 0) {
                blocksWithErrors.push(bId);
            }
        });
        let errorMessage = "Please correct the datalog input";
        if (((_d = result.Err) === null || _d === void 0 ? void 0 : _d.Biscuit) === "InternalError") {
            errorMessage = "Please provide an authority block";
        }
        else if (typeof ((_e = result.Err) === null || _e === void 0 ? void 0 : _e.Biscuit) === "object" &&
            ((_h = (_g = (_f = result.Err) === null || _f === void 0 ? void 0 : _f.Biscuit) === null || _g === void 0 ? void 0 : _g.Format) === null || _h === void 0 ? void 0 : _h.InvalidKeySize) !== undefined) {
            errorMessage = "Please enter (or generate) a valid private key";
        }
        else if (blocksWithErrors.length > 0) {
            const blockList = blocksWithErrors
                .map((bId) => (bId === 0 ? "authority" : bId.toString()))
                .join(", ");
            errorMessage =
                "Please correct the datalog input on the following blocks: " +
                    blockList;
        }
        const token = (_j = result.Ok) !== null && _j !== void 0 ? _j : errorMessage;
        return x `
      <div class="row">
        <div class="code">
          ${this.renderKeyInput()}
          ${this._blocks.map((code, id) => {
            var _a, _b, _c;
            const blockErrors = (_c = (_b = (_a = result.Err) === null || _a === void 0 ? void 0 : _a.Parse) === null || _b === void 0 ? void 0 : _b.blocks[id]) !== null && _c !== void 0 ? _c : [];
            return this.renderBlock(id, blockErrors);
        })}
          <button @click=${this.addBlock}>Add block</button>
        </div>
        <div class="content">
          <p>Generated token</p>
          <code>${token}</code>
        </div>
      </div>
    `;
    }
};
BcTokenGenerator.styles = i$3 `
    .row {
      display: flex;
      flex-direction: column;
      text-align: left;
    }

    @media (prefers-color-scheme: dark) {
      .row {
        color: #dee2e6;
        background: #131314;
      }
      textarea {
        color: #dee2e6;
        background: #131314;
      }
    }

    @media (prefers-color-scheme: light) {
      .row {
        color: #1d2d35;
        background: #fff;
      }
    }

    @media (min-width: 576px) {
      .row {
        display: flex;
        flex-flow: row wrap;
        flex-direction: row;
      }

      .code {
        order: 1;
        width: 49%;
      }

      .content {
        order: 2;
        width: 49%;
      }
    }

    code {
      overflow-wrap: anywhere;
      padding: 0.2em;
      padding-top: 1em;
    }

    .code {
      border: 1px rgba(128, 128, 128, 0.4) solid;
      display: flex;
      flex-direction: column;
    }

    .content code {
      user-select: all;
    }

    textarea {
      flex-grow: 1;
      border: 0;
    }

    .content {
      border-top: 1px rgba(128, 128, 128, 0.4) solid;
      border-right: 1px rgba(128, 128, 128, 0.4) solid;
    }

    p {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
      margin-block-start: 0px;
      margin-block-end: 0px;
      padding: 0.2em;
      font-size: 0.8em;
    }

    bc-datalog-editor {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
    }
  `;
__decorate([
    n$1()
], BcTokenGenerator.prototype, "privateKey", void 0);
__decorate([
    t$1()
], BcTokenGenerator.prototype, "_alg", void 0);
__decorate([
    t$1()
], BcTokenGenerator.prototype, "_blocks", void 0);
__decorate([
    t$1()
], BcTokenGenerator.prototype, "_started", void 0);
BcTokenGenerator = __decorate([
    e$3("bc-token-generator")
], BcTokenGenerator);

/**
 * TODO DOCS
 */
let BcSnapshotPrinter = class BcSnapshotPrinter extends s {
    constructor() {
        var _a;
        super();
        this.snapshot = "";
        this.readonly = false;
        this.showQuery = false;
        this.authorizer = "";
        this.query = "";
        this._started = false;
        const authorizerChild = this.querySelector(".authorizer");
        if (authorizerChild !== null) {
            this.authorizer = trimLines((_a = authorizerChild.textContent) !== null && _a !== void 0 ? _a : "");
        }
    }
    firstUpdated() {
        initialize().then(() => (this._started = true));
    }
    _onUpdatedSnapshot(e) {
        if (this.readonly)
            return;
        this.snapshot = e.target.value.trim();
    }
    _onUpdatedQuery(e) {
        if (!this.showQuery)
            return;
        this.query = e.detail.code;
    }
    renderSnapshotInput() {
        if (this.readonly) {
            return x `
        <div class="code">
          <p>Encoded snapshot</p>
          <code>${this.snapshot}</code>
        </div>
      `;
        }
        return x `
      <div class="code">
        <p>Encoded snapshot</p>
        <textarea @input=${this._onUpdatedSnapshot}>${this.snapshot}</textarea>
      </div>
    `;
    }
    renderNotStarted() {
        return x `
      <div class="snapshot">
        ${this.renderSnapshotInput()}
        <div class="content">loading biscuit snapshot</div>
      </div>
    `;
    }
    renderEmptyToken() {
        return x `
      ${this.renderSnapshotInput()}
      <div class="content">Please enter a base64-encoded snapshot</div>
    `;
    }
    renderResult(result) {
        if (this.snapshot === "") {
            return x `
        ${this.renderSnapshotInput()}
        <div class="content">Please enter a base64-encoded snapshot</div>
      `;
        }
        if (result.snapshot.Err) {
            return x `
        ${this.renderSnapshotInput()}
        <div class="content">${result.snapshot.Err}</div>
      `;
        }
        const { code, elapsed_micros, iterations, authorization_result, query_result, } = result.snapshot.Ok;
        const auth_res = {
            Ok: {
                authorizer_editor: { markers: [] },
                authorizer_result: authorization_result,
                authorizer_world: [],
                token_blocks: [],
            },
        };
        return x `
      ${this.renderSnapshotInput()}
      <div class="content">
        <p>Elapsed time: ${this.renderDuration(elapsed_micros)}</p>
        <p>Iterations: ${iterations}</p>
        <p>Snapshot contents</p>
        <bc-datalog-editor code=${code} .marks=${[]} readonly="true">
        </bc-datalog-editor>
        <bc-authorizer-result .content=${auth_res}></bc-authorizer-result>
        ${this.renderQueryInput()}
        ${query_result && this.renderQueryResult(query_result)}
      </div>
    `;
    }
    renderDuration(micros) {
        if (micros === undefined) {
            return "n/a";
        }
        return `${micros}μs`;
    }
    renderQueryInput() {
        if (!this.showQuery)
            return;
        return x `
      <p>Query snapshot</p>
      <bc-datalog-editor
        @bc-datalog-editor:update=${(e) => this._onUpdatedQuery(e)}
      >
      </bc-datalog-editor>
    `;
    }
    renderQueryResult(result) {
        if (result.Err) {
            console.error(result.Err);
            return x `<p>error</p>`;
        }
        else {
            return x `<bc-authorizer-content .content=${result.Ok}>
      </bc-authorizer-content>`;
        }
    }
    render() {
        if (!this._started)
            return this.renderNotStarted();
        const inspectQuery = {
            data: this.snapshot,
            extra_authorizer: this.authorizer != "" ? this.authorizer : undefined,
            query: this.query != "" ? this.query : undefined,
        };
        const parseResult = inspect_snapshot(inspectQuery);
        return x ` <div class="row">${this.renderResult(parseResult)}</div> `;
    }
};
BcSnapshotPrinter.styles = i$3 `
    .row {
      display: flex;
      flex-direction: column;
      text-align: left;
    }

    .public-key-input {
      display: flex;
    }

    .public-key-input label {
      height: 1.5em;
    }

    #public-key {
      margin-left: 1em;
      flex-grow: 1;
    }

    @media (prefers-color-scheme: dark) {
      .row {
        color: #dee2e6;
        background: #131314;
      }
      textarea {
        color: #dee2e6;
        background: #131314;
      }
    }

    @media (prefers-color-scheme: light) {
      .row {
        color: #1d2d35;
        background: #fff;
      }
    }

    @media (min-width: 576px) {
      .row {
        display: flex;
        flex-flow: row wrap;
        flex-direction: row;
      }

      .code {
        order: 1;
        width: 49%;
      }

      .content {
        order: 2;
        width: 49%;
      }
    }

    code {
      overflow-wrap: anywhere;
      padding: 0.2em;
      padding-top: 1em;
    }

    .code {
      border: 1px rgba(128, 128, 128, 0.4) solid;
      display: flex;
      flex-direction: column;
    }

    textarea {
      flex-grow: 1;
      border: 0;
    }

    .content {
      border-top: 1px rgba(128, 128, 128, 0.4) solid;
      border-right: 1px rgba(128, 128, 128, 0.4) solid;
    }

    p {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
      margin-block-start: 0px;
      margin-block-end: 0px;
      padding: 0.2em;
      font-size: 0.8em;
    }

    .revocation-id,
    .external-key {
      overflow: hidden;
      diplay: inline-block;
      text-overflow: ellipsis;
      max-width: 100;
    }

    .revocation-id,
    .external-key > .id {
      user-select: all;
    }

    .content code {
      user-select: all;
    }

    bc-datalog-editor {
      border-bottom: 1px rgba(128, 128, 128, 0.4) solid;
    }
  `;
__decorate([
    n$1()
], BcSnapshotPrinter.prototype, "snapshot", void 0);
__decorate([
    n$1()
], BcSnapshotPrinter.prototype, "readonly", void 0);
__decorate([
    n$1()
], BcSnapshotPrinter.prototype, "showQuery", void 0);
__decorate([
    n$1()
], BcSnapshotPrinter.prototype, "authorizer", void 0);
__decorate([
    n$1()
], BcSnapshotPrinter.prototype, "query", void 0);
__decorate([
    t$1()
], BcSnapshotPrinter.prototype, "_started", void 0);
BcSnapshotPrinter = __decorate([
    e$3("bc-snapshot-printer")
], BcSnapshotPrinter);

async function setup() {
    await initialize();
    console.log('Biscuit web components initialized');
}

setup();

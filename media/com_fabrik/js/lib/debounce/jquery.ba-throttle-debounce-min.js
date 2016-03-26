/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 *
 * ALTERED FOR FABRIK!!!
 */
require(["fab/fabrik"],function(n){!function(t,u){"$:nomunge";var e,o=t.jQuery||t.Cowboy||(t.Cowboy={});n.throttle=e=function(n,t,e,i){function r(){function o(){f=+new Date,e.apply(c,b)}function r(){a=u}var c=this,g=+new Date-f,b=arguments;i&&!a&&o(),a&&clearTimeout(a),i===u&&g>n?o():t!==!0&&(a=setTimeout(i?r:o,i===u?n-g:n))}var a,f=0;return"boolean"!=typeof t&&(i=e,e=t,t=u),o.guid&&(r.guid=e.guid=e.guid||o.guid++),r},n.debounce=function(n,t,o){return o===u?e(n,t,!1):e(n,o,t!==!1)}}(this)});
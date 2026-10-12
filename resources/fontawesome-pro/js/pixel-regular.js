/*!
 * Font Awesome Pro 7.3.0 by @fontawesome - https://fontawesome.com
 * License - https://fontawesome.com/license (Commercial License)
 * Copyright 2026 Fonticons, Inc.
 */
(function () {
  'use strict';

  var _WINDOW = {};
  var _DOCUMENT = {};
  try {
    if (typeof window !== 'undefined') _WINDOW = window;
    if (typeof document !== 'undefined') _DOCUMENT = document;
  } catch (e) {} // eslint-disable-line no-empty

  var _ref = _WINDOW.navigator || {},
    _ref$userAgent = _ref.userAgent,
    userAgent = _ref$userAgent === void 0 ? '' : _ref$userAgent;
  var WINDOW = _WINDOW;
  var DOCUMENT = _DOCUMENT;
  var IS_BROWSER = !!WINDOW.document;
  var IS_DOM = !!DOCUMENT.documentElement && !!DOCUMENT.head && typeof DOCUMENT.addEventListener === 'function' && typeof DOCUMENT.createElement === 'function';
  var IS_IE = ~userAgent.indexOf('MSIE') || ~userAgent.indexOf('Trident/');

  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithoutHoles(r) {
    if (Array.isArray(r)) return _arrayLikeToArray(r);
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: !0,
      configurable: !0,
      writable: !0
    }) : e[r] = t, e;
  }
  function _iterableToArray(r) {
    if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
  }
  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  function ownKeys(e, r) {
    var t = Object.keys(e);
    if (Object.getOwnPropertySymbols) {
      var o = Object.getOwnPropertySymbols(e);
      r && (o = o.filter(function (r) {
        return Object.getOwnPropertyDescriptor(e, r).enumerable;
      })), t.push.apply(t, o);
    }
    return t;
  }
  function _objectSpread2(e) {
    for (var r = 1; r < arguments.length; r++) {
      var t = null != arguments[r] ? arguments[r] : {};
      r % 2 ? ownKeys(Object(t), !0).forEach(function (r) {
        _defineProperty(e, r, t[r]);
      }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
        Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
      });
    }
    return e;
  }
  function _toConsumableArray(r) {
    return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread();
  }
  function _toPrimitive(t, r) {
    if ("object" != typeof t || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r || "default");
      if ("object" != typeof i) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }
  function _toPropertyKey(t) {
    var i = _toPrimitive(t, "string");
    return "symbol" == typeof i ? i : i + "";
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

  var _cl;
  var rl = {
      classic: {
        fa: "solid",
        fas: "solid",
        "fa-solid": "solid",
        far: "regular",
        "fa-regular": "regular",
        fal: "light",
        "fa-light": "light",
        fat: "thin",
        "fa-thin": "thin",
        fab: "brands",
        "fa-brands": "brands"
      },
      duotone: {
        fa: "solid",
        fad: "solid",
        "fa-solid": "solid",
        "fa-duotone": "solid",
        fadr: "regular",
        "fa-regular": "regular",
        fadl: "light",
        "fa-light": "light",
        fadt: "thin",
        "fa-thin": "thin"
      },
      sharp: {
        fa: "solid",
        fass: "solid",
        "fa-solid": "solid",
        fasr: "regular",
        "fa-regular": "regular",
        fasl: "light",
        "fa-light": "light",
        fast: "thin",
        "fa-thin": "thin"
      },
      "sharp-duotone": {
        fa: "solid",
        fasds: "solid",
        "fa-solid": "solid",
        fasdr: "regular",
        "fa-regular": "regular",
        fasdl: "light",
        "fa-light": "light",
        fasdt: "thin",
        "fa-thin": "thin"
      },
      slab: {
        "fa-regular": "regular",
        faslr: "regular"
      },
      "slab-press": {
        "fa-regular": "regular",
        faslpr: "regular"
      },
      "slab-duo": {
        "fa-regular": "regular",
        fasldr: "regular"
      },
      "slab-press-duo": {
        "fa-regular": "regular",
        faslpdr: "regular"
      },
      thumbprint: {
        "fa-light": "light",
        fatl: "light"
      },
      vellum: {
        "fa-solid": "solid",
        favs: "solid"
      },
      pixel: {
        "fa-regular": "regular",
        fapr: "regular"
      },
      mosaic: {
        "fa-solid": "solid",
        fams: "solid"
      },
      whiteboard: {
        "fa-semibold": "semibold",
        fawsb: "semibold"
      },
      notdog: {
        "fa-solid": "solid",
        fans: "solid"
      },
      "notdog-duo": {
        "fa-solid": "solid",
        fands: "solid"
      },
      etch: {
        "fa-solid": "solid",
        faes: "solid"
      },
      graphite: {
        "fa-thin": "thin",
        fagt: "thin"
      },
      jelly: {
        "fa-regular": "regular",
        fajr: "regular"
      },
      "jelly-fill": {
        "fa-regular": "regular",
        fajfr: "regular"
      },
      "jelly-duo": {
        "fa-regular": "regular",
        fajdr: "regular"
      },
      chisel: {
        "fa-regular": "regular",
        facr: "regular"
      },
      utility: {
        "fa-semibold": "semibold",
        fausb: "semibold"
      },
      "utility-duo": {
        "fa-semibold": "semibold",
        faudsb: "semibold"
      },
      "utility-fill": {
        "fa-semibold": "semibold",
        faufsb: "semibold"
      }
    };
  var u = "classic",
    l = "duotone",
    h = "sharp",
    t = "sharp-duotone",
    g = "chisel",
    n = "etch",
    m = "graphite",
    p = "jelly",
    s = "jelly-duo",
    y = "jelly-fill",
    w = "mosaic",
    x = "notdog",
    e = "notdog-duo",
    b = "pixel",
    c = "slab",
    o = "slab-duo",
    I = "slab-press",
    a = "slab-press-duo",
    r = "thumbprint",
    v = "utility",
    i = "utility-duo",
    F = "utility-fill",
    d = "vellum",
    S = "whiteboard",
    A = "Classic",
    P = "Duotone",
    j = "Sharp",
    B = "Sharp Duotone",
    N = "Chisel",
    D = "Etch",
    k = "Graphite",
    T = "Jelly",
    C = "Jelly Duo",
    W = "Jelly Fill",
    R = "Mosaic",
    K = "Notdog",
    L = "Notdog Duo",
    U = "Pixel",
    J = "Slab",
    _ = "Slab Duo",
    M = "Slab Press",
    E = "Slab Press Duo",
    G = "Thumbprint",
    V = "Utility",
    z = "Utility Duo",
    O = "Utility Fill",
    Y = "Vellum",
    q = "Whiteboard",
    cl = (_cl = {}, _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_cl, u, A), l, P), h, j), t, B), g, N), n, D), m, k), p, T), s, C), y, W), _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_cl, w, R), x, K), e, L), b, U), c, J), o, _), I, M), a, E), r, G), v, V), _defineProperty(_defineProperty(_defineProperty(_defineProperty(_cl, i, z), F, O), d, Y), S, q));
  var Al = {
      classic: {
        900: "fas",
        400: "far",
        normal: "far",
        300: "fal",
        100: "fat"
      },
      duotone: {
        900: "fad",
        400: "fadr",
        300: "fadl",
        100: "fadt"
      },
      sharp: {
        900: "fass",
        400: "fasr",
        300: "fasl",
        100: "fast"
      },
      "sharp-duotone": {
        900: "fasds",
        400: "fasdr",
        300: "fasdl",
        100: "fasdt"
      },
      slab: {
        400: "faslr"
      },
      "slab-press": {
        400: "faslpr"
      },
      "slab-duo": {
        400: "fasldr"
      },
      "slab-press-duo": {
        400: "faslpdr"
      },
      vellum: {
        900: "favs"
      },
      mosaic: {
        900: "fams"
      },
      pixel: {
        400: "fapr"
      },
      whiteboard: {
        600: "fawsb"
      },
      thumbprint: {
        300: "fatl"
      },
      notdog: {
        900: "fans"
      },
      "notdog-duo": {
        900: "fands"
      },
      etch: {
        900: "faes"
      },
      graphite: {
        100: "fagt"
      },
      chisel: {
        400: "facr"
      },
      jelly: {
        400: "fajr"
      },
      "jelly-fill": {
        400: "fajfr"
      },
      "jelly-duo": {
        400: "fajdr"
      },
      utility: {
        600: "fausb"
      },
      "utility-duo": {
        600: "faudsb"
      },
      "utility-fill": {
        600: "faufsb"
      }
    };
  var $l = {
      chisel: {
        regular: "facr"
      },
      classic: {
        brands: "fab",
        light: "fal",
        regular: "far",
        solid: "fas",
        thin: "fat"
      },
      duotone: {
        light: "fadl",
        regular: "fadr",
        solid: "fad",
        thin: "fadt"
      },
      etch: {
        solid: "faes"
      },
      graphite: {
        thin: "fagt"
      },
      jelly: {
        regular: "fajr"
      },
      "jelly-duo": {
        regular: "fajdr"
      },
      "jelly-fill": {
        regular: "fajfr"
      },
      mosaic: {
        solid: "fams"
      },
      notdog: {
        solid: "fans"
      },
      "notdog-duo": {
        solid: "fands"
      },
      pixel: {
        regular: "fapr"
      },
      sharp: {
        light: "fasl",
        regular: "fasr",
        solid: "fass",
        thin: "fast"
      },
      "sharp-duotone": {
        light: "fasdl",
        regular: "fasdr",
        solid: "fasds",
        thin: "fasdt"
      },
      slab: {
        regular: "faslr"
      },
      "slab-duo": {
        regular: "fasldr"
      },
      "slab-press": {
        regular: "faslpr"
      },
      "slab-press-duo": {
        regular: "faslpdr"
      },
      thumbprint: {
        light: "fatl"
      },
      utility: {
        semibold: "fausb"
      },
      "utility-duo": {
        semibold: "faudsb"
      },
      "utility-fill": {
        semibold: "faufsb"
      },
      vellum: {
        solid: "favs"
      },
      whiteboard: {
        semibold: "fawsb"
      }
    };
  var rt = {
      kit: {
        fak: "kit",
        "fa-kit": "kit"
      },
      "kit-duotone": {
        fakd: "kit-duotone",
        "fa-kit-duotone": "kit-duotone"
      }
    },
    it = ["kit"];
  var H = "kit",
    f = "kit-duotone",
    Q = "Kit",
    X = "Kit Duotone",
    ht = _defineProperty(_defineProperty({}, H, Q), f, X);
  var mt = {
    kit: {
      "fa-kit": "fak"
    },
    "kit-duotone": {
      "fa-kit-duotone": "fakd"
    }
  };
  var It = {
      kit: {
        fak: "fa-kit"
      },
      "kit-duotone": {
        fakd: "fa-kit-duotone"
      }
    };
  var St = {
      kit: {
        kit: "fak"
      },
      "kit-duotone": {
        "kit-duotone": "fakd"
      }
    };

  var _jl;
  var l$1 = {
      GROUP: "duotone-group",
      SWAP_OPACITY: "swap-opacity",
      PRIMARY: "primary",
      SECONDARY: "secondary"
    };
  var g$1 = "classic",
    a$1 = "duotone",
    m$1 = "sharp",
    t$1 = "sharp-duotone",
    p$1 = "chisel",
    y$1 = "etch",
    b$1 = "graphite",
    w$1 = "jelly",
    e$1 = "jelly-duo",
    x$1 = "jelly-fill",
    c$1 = "mosaic",
    I$1 = "notdog",
    o$1 = "notdog-duo",
    v$1 = "pixel",
    F$1 = "slab",
    r$1 = "slab-duo",
    S$1 = "slab-press",
    i$1 = "slab-press-duo",
    d$1 = "thumbprint",
    A$1 = "utility",
    f$1 = "utility-duo",
    P$1 = "utility-fill",
    u$1 = "vellum",
    j$1 = "whiteboard",
    B$1 = "Classic",
    N$1 = "Duotone",
    D$1 = "Sharp",
    k$1 = "Sharp Duotone",
    C$1 = "Chisel",
    T$1 = "Etch",
    L$1 = "Graphite",
    W$1 = "Jelly",
    R$1 = "Jelly Duo",
    K$1 = "Jelly Fill",
    U$1 = "Mosaic",
    J$1 = "Notdog",
    _$1 = "Notdog Duo",
    M$1 = "Pixel",
    E$1 = "Slab",
    G$1 = "Slab Duo",
    O$1 = "Slab Press",
    V$1 = "Slab Press Duo",
    z$1 = "Thumbprint",
    Y$1 = "Utility",
    $$1 = "Utility Duo",
    q$1 = "Utility Fill",
    H$1 = "Vellum",
    Q$1 = "Whiteboard",
    jl$1 = (_jl = {}, _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_jl, g$1, B$1), a$1, N$1), m$1, D$1), t$1, k$1), p$1, C$1), y$1, T$1), b$1, L$1), w$1, W$1), e$1, R$1), x$1, K$1), _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_jl, c$1, U$1), I$1, J$1), o$1, _$1), v$1, M$1), F$1, E$1), r$1, G$1), S$1, O$1), i$1, V$1), d$1, z$1), A$1, Y$1), _defineProperty(_defineProperty(_defineProperty(_defineProperty(_jl, f$1, $$1), P$1, q$1), u$1, H$1), j$1, Q$1));
  var X$1 = "kit",
    h$1 = "kit-duotone",
    Z$1 = "Kit",
    ll$1 = "Kit Duotone",
    ws = _defineProperty(_defineProperty({}, X$1, Z$1), h$1, ll$1);
  var ra = {
      classic: {
        "fa-brands": "fab",
        "fa-duotone": "fad",
        "fa-light": "fal",
        "fa-regular": "far",
        "fa-solid": "fas",
        "fa-thin": "fat"
      },
      duotone: {
        "fa-regular": "fadr",
        "fa-light": "fadl",
        "fa-thin": "fadt"
      },
      sharp: {
        "fa-solid": "fass",
        "fa-regular": "fasr",
        "fa-light": "fasl",
        "fa-thin": "fast"
      },
      "sharp-duotone": {
        "fa-solid": "fasds",
        "fa-regular": "fasdr",
        "fa-light": "fasdl",
        "fa-thin": "fasdt"
      },
      slab: {
        "fa-regular": "faslr"
      },
      "slab-press": {
        "fa-regular": "faslpr"
      },
      "slab-duo": {
        "fa-regular": "fasldr"
      },
      "slab-press-duo": {
        "fa-regular": "faslpdr"
      },
      pixel: {
        "fa-regular": "fapr"
      },
      mosaic: {
        "fa-solid": "fams"
      },
      vellum: {
        "fa-solid": "favs"
      },
      whiteboard: {
        "fa-semibold": "fawsb"
      },
      thumbprint: {
        "fa-light": "fatl"
      },
      notdog: {
        "fa-solid": "fans"
      },
      "notdog-duo": {
        "fa-solid": "fands"
      },
      etch: {
        "fa-solid": "faes"
      },
      graphite: {
        "fa-thin": "fagt"
      },
      jelly: {
        "fa-regular": "fajr"
      },
      "jelly-fill": {
        "fa-regular": "fajfr"
      },
      "jelly-duo": {
        "fa-regular": "fajdr"
      },
      chisel: {
        "fa-regular": "facr"
      },
      utility: {
        "fa-semibold": "fausb"
      },
      "utility-duo": {
        "fa-semibold": "faudsb"
      },
      "utility-fill": {
        "fa-semibold": "faufsb"
      }
    },
    al$1 = {
      classic: ["fas", "far", "fal", "fat", "fad"],
      duotone: ["fadr", "fadl", "fadt"],
      sharp: ["fass", "fasr", "fasl", "fast"],
      "sharp-duotone": ["fasds", "fasdr", "fasdl", "fasdt"],
      slab: ["faslr"],
      "slab-press": ["faslpr"],
      "slab-duo": ["fasldr"],
      "slab-press-duo": ["faslpdr"],
      pixel: ["fapr"],
      mosaic: ["fams"],
      vellum: ["favs"],
      whiteboard: ["fawsb"],
      thumbprint: ["fatl"],
      notdog: ["fans"],
      "notdog-duo": ["fands"],
      etch: ["faes"],
      graphite: ["fagt"],
      jelly: ["fajr"],
      "jelly-fill": ["fajfr"],
      "jelly-duo": ["fajdr"],
      chisel: ["facr"],
      utility: ["fausb"],
      "utility-duo": ["faudsb"],
      "utility-fill": ["faufsb"]
    },
    da = {
      classic: {
        fab: "fa-brands",
        fad: "fa-duotone",
        fal: "fa-light",
        far: "fa-regular",
        fas: "fa-solid",
        fat: "fa-thin"
      },
      duotone: {
        fadr: "fa-regular",
        fadl: "fa-light",
        fadt: "fa-thin"
      },
      sharp: {
        fass: "fa-solid",
        fasr: "fa-regular",
        fasl: "fa-light",
        fast: "fa-thin"
      },
      "sharp-duotone": {
        fasds: "fa-solid",
        fasdr: "fa-regular",
        fasdl: "fa-light",
        fasdt: "fa-thin"
      },
      slab: {
        faslr: "fa-regular"
      },
      "slab-press": {
        faslpr: "fa-regular"
      },
      "slab-duo": {
        fasldr: "fa-regular"
      },
      "slab-press-duo": {
        faslpdr: "fa-regular"
      },
      pixel: {
        fapr: "fa-regular"
      },
      mosaic: {
        fams: "fa-solid"
      },
      vellum: {
        favs: "fa-solid"
      },
      whiteboard: {
        fawsb: "fa-semibold"
      },
      thumbprint: {
        fatl: "fa-light"
      },
      notdog: {
        fans: "fa-solid"
      },
      "notdog-duo": {
        fands: "fa-solid"
      },
      etch: {
        faes: "fa-solid"
      },
      graphite: {
        fagt: "fa-thin"
      },
      jelly: {
        fajr: "fa-regular"
      },
      "jelly-fill": {
        fajfr: "fa-regular"
      },
      "jelly-duo": {
        fajdr: "fa-regular"
      },
      chisel: {
        facr: "fa-regular"
      },
      utility: {
        fausb: "fa-semibold"
      },
      "utility-duo": {
        faudsb: "fa-semibold"
      },
      "utility-fill": {
        faufsb: "fa-semibold"
      }
    },
    el$1 = ["solid", "regular", "light", "thin", "duotone", "brands", "semibold"],
    sl$1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    ol$1 = sl$1.concat([11, 12, 13, 14, 15, 16, 17, 18, 19, 20]),
    rl$1 = ["aw", "fw", "pull-left", "pull-right"],
    ga = [].concat(_toConsumableArray(Object.keys(al$1)), el$1, rl$1, ["2xs", "xs", "sm", "lg", "xl", "2xl", "beat", "beat-fade", "border", "bounce", "buzz", "canvas-square", "canvas-roomy", "fade", "flip-360", "flip-both", "flip-horizontal", "flip-vertical", "flip", "float", "inverse", "jello", "layers", "layers-bottom-left", "layers-bottom-right", "layers-counter", "layers-text", "layers-top-left", "layers-top-right", "li", "pull-end", "pull-start", "pulse", "rotate-180", "rotate-270", "rotate-90", "rotate-by", "shake", "spin-pulse", "spin-reverse", "spin", "spin-snap", "spin-snap-4", "spin-snap-8", "stack-1x", "stack-2x", "stack", "swing", "ul", "wag", "width-auto", "width-fixed", l$1.GROUP, l$1.SWAP_OPACITY, l$1.PRIMARY, l$1.SECONDARY]).concat(sl$1.map(function (s) {
      return "".concat(s, "x");
    })).concat(ol$1.map(function (s) {
      return "w-".concat(s);
    }));

  var NAMESPACE_IDENTIFIER = '___FONT_AWESOME___';
  var PRODUCTION = function () {
    try {
      return "production" === 'production';
    } catch (e$$1) {
      return false;
    }
  }();
  function familyProxy(obj) {
    // Defaults to the classic family if family is not available
    return new Proxy(obj, {
      get: function get(target, prop) {
        return prop in target ? target[prop] : target[u];
      }
    });
  }
  var _PREFIX_TO_STYLE = _objectSpread2({}, rl);

  // We changed FACSSClassesToStyleId in the icons repo to be canonical and as such, "classic" family does not have any
  // duotone styles.  But we do still need duotone in _PREFIX_TO_STYLE below, so we are manually adding
  // {'fa-duotone': 'duotone'}
  _PREFIX_TO_STYLE[u] = _objectSpread2(_objectSpread2(_objectSpread2(_objectSpread2({}, {
    'fa-duotone': 'duotone'
  }), rl[u]), rt['kit']), rt['kit-duotone']);
  var PREFIX_TO_STYLE = familyProxy(_PREFIX_TO_STYLE);
  var _STYLE_TO_PREFIX = _objectSpread2({}, $l);

  // We changed FAStyleIdToShortPrefixId in the icons repo to be canonical and as such, "classic" family does not have any
  // duotone styles.  But we do still need duotone in _STYLE_TO_PREFIX below, so we are manually adding {duotone: 'fad'}
  _STYLE_TO_PREFIX[u] = _objectSpread2(_objectSpread2(_objectSpread2(_objectSpread2({}, {
    duotone: 'fad'
  }), _STYLE_TO_PREFIX[u]), St['kit']), St['kit-duotone']);
  var STYLE_TO_PREFIX = familyProxy(_STYLE_TO_PREFIX);
  var _PREFIX_TO_LONG_STYLE = _objectSpread2({}, da);
  _PREFIX_TO_LONG_STYLE[u] = _objectSpread2(_objectSpread2({}, _PREFIX_TO_LONG_STYLE[u]), It['kit']);
  var PREFIX_TO_LONG_STYLE = familyProxy(_PREFIX_TO_LONG_STYLE);
  var _LONG_STYLE_TO_PREFIX = _objectSpread2({}, ra);
  _LONG_STYLE_TO_PREFIX[u] = _objectSpread2(_objectSpread2({}, _LONG_STYLE_TO_PREFIX[u]), mt['kit']);
  var LONG_STYLE_TO_PREFIX = familyProxy(_LONG_STYLE_TO_PREFIX);
  var _FONT_WEIGHT_TO_PREFIX = _objectSpread2({}, Al);
  var FONT_WEIGHT_TO_PREFIX = familyProxy(_FONT_WEIGHT_TO_PREFIX);
  var RESERVED_CLASSES = [].concat(_toConsumableArray(it), _toConsumableArray(ga));

  function bunker(fn) {
    try {
      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        args[_key - 1] = arguments[_key];
      }
      fn.apply(void 0, args);
    } catch (e) {
      if (!PRODUCTION) {
        throw e;
      }
    }
  }

  var w$2 = WINDOW || {};
  if (!w$2[NAMESPACE_IDENTIFIER]) w$2[NAMESPACE_IDENTIFIER] = {};
  if (!w$2[NAMESPACE_IDENTIFIER].styles) w$2[NAMESPACE_IDENTIFIER].styles = {};
  if (!w$2[NAMESPACE_IDENTIFIER].hooks) w$2[NAMESPACE_IDENTIFIER].hooks = {};
  if (!w$2[NAMESPACE_IDENTIFIER].shims) w$2[NAMESPACE_IDENTIFIER].shims = [];
  var namespace = w$2[NAMESPACE_IDENTIFIER];

  function normalizeIcons(icons) {
    return Object.keys(icons).reduce(function (acc, iconName) {
      var icon = icons[iconName];
      var expanded = !!icon.icon;
      if (expanded) {
        acc[icon.iconName] = icon.icon;
      } else {
        acc[iconName] = icon;
      }
      return acc;
    }, {});
  }
  function defineIcons(prefix, icons) {
    var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    var _params$skipHooks = params.skipHooks,
      skipHooks = _params$skipHooks === void 0 ? false : _params$skipHooks;
    var normalized = normalizeIcons(icons);
    if (typeof namespace.hooks.addPack === 'function' && !skipHooks) {
      namespace.hooks.addPack(prefix, normalizeIcons(icons));
    } else {
      namespace.styles[prefix] = _objectSpread2(_objectSpread2({}, namespace.styles[prefix] || {}), normalized);
    }

    /**
     * Font Awesome 4 used the prefix of `fa` for all icons. With the introduction
     * of new styles we needed to differentiate between them. Prefix `fa` is now an alias
     * for `fas` so we'll ease the upgrade process for our users by automatically defining
     * this as well.
     */
    if (prefix === 'fas') {
      defineIcons('fa', icons);
    }
  }

  var icons = {
    "question": [448, 560, [10067, 10068, 61736], "3f", "M112-28l0 56 224 0 0-56-224 0zM56 28l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 84l0 112 56 0 0-112-56 0zm392 0l0 224 56 0 0-224-56 0zM336 308l0 56 56 0 0-56-56 0zM196 364l0 56 140 0 0-56-140 0zm0 112l0 112 56 0 0-112-56 0z"],
    "hand-point-left": [560, 560, [], "f0a5", "M336 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM0 168l0 56 392 0 0-56-392 0zm504 0l0 336 56 0 0-336-56 0zM168 280l0 56 112 0 0-56-112 0zm0 112l0 56 112 0 0-56-112 0zm56 112l0 56 280 0 0-56-280 0z"],
    "minus": [504, 560, [8211, 8722, 10134, "subtract"], "f068", "M504 308l-504 0 0-56 504 0z"],
    "microphone-slash": [560, 560, [], "f131", "M392 560l-224 0 0-56 84 0 0-56-84 0 0-56 140 0 0 112 84 0 0 56zm168 0l-56 0 0-56 56 0 0 56zm-56-56l-56 0 0-56 56 0 0 56zm-56-56l-56 0 0-56 56 0 0 56zM168 392l-56 0 0-56 56 0 0 56zm224 0l-56 0 0-56 56 0 0 56zM112 336l-56 0 0-112 56 0 0 112zm224 0l-56 0 0-56 56 0 0 56zm168 0l-56 0 0-112 56 0 0 112zM280 280l-56 0 0-56 56 0 0 56zm-56-56l-56 0 0-56 56 0 0 56zm168 0l-56 0 0-168 56 0 0 168zM168 168l-56 0 0-56 56 0 0 56zm-56-56l-56 0 0-56 56 0 0 56zM56 56L0 56 0 0 56 0 56 56zm280 0l-112 0 0-56 112 0 0 56z"],
    "car": [616, 560, [128664, "automobile"], "f1b9", "M168 28l0 56 280 0 0-56-280 0zM112 84l0 112-112 0 0 56 56 0 0 280 56 0 0-56 392 0 0 56 56 0 0-280 56 0 0-56-112 0 0-112-56 0 0 112-280 0 0-112-56 0zm0 336l0-168 392 0 0 168-392 0zm56-112l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "filter": [504, 560, [], "f0b0", "M56 0l0 56 392 0 0-56-392 0zM0 56l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM56 168l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 280l0 224 56 0 0-224-56 0zm112 0l0 224-56 0 0 56 112 0 0-280-56 0z"],
    "envelope": [560, 560, [128386, 9993, 61443], "f0e0", "M56 56l0 56-56 0 0 336 56 0 0-280 56 0 0-56 336 0 0 56 56 0 0 280 56 0 0-336-56 0 0-56-448 0zm56 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 224l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 280l0 56 112 0 0-56-112 0zM56 448l0 56 448 0 0-56-448 0z"],
    "at": [560, 560, [61946], "40", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm224 0l0 56 112 0 0-56-112 0zm280 0l0 168 56 0 0-168-56 0zM168 224l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM224 336l0 56 112 0 0-56-112 0zm168 0l0 56 112 0 0-56-112 0zM56 392l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 280 0 0-56-280 0z"],
    "truck": [616, 560, [128666, 9951], "f0d1", "M56 28l0 56 336 0 0 308-336 0 0 56 28 0 0 56 56 0 0-56 56 0 0 56 56 0 0-56 140 0 0 56 56 0 0-56 56 0 0 56 56 0 0-56 56 0 0-252-56 0 0-56-112 0 0-112-392 0zM560 196l0 56-56 0 0 56 56 0 0 84-112 0 0-196 112 0zM0 84l0 308 56 0 0-308-56 0zM140 504l0 56 56 0 0-56-56 0zm308 0l0 56 56 0 0-56-56 0z"],
    "game-console-handheld-crank": [672, 560, [], "e5b9", "M112 56l0 56 336 0 0-56-336 0zM56 112l0 336 56 0 0-336-56 0zm392 0l0 336 56 0 0-112 112 0 0-112 56 0 0-56-112 0 0 112-56 0 0-168-56 0zM168 168l0 168 224 0 0-168-224 0zm56 112l0-56 112 0 0 56-112 0zM112 448l0 56 336 0 0-56-336 0z"],
    "caret-right": [336, 560, [], "f0da", "M56 28l0 504 56 0 0-56 56 0 0-56-56 0 0-280 56 0 0-56-56 0 0-56-56 0zM168 140l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "bell": [504, 560, [128276, 61602], "f0f3", "M168 0l0 56 168 0 0-56-168 0zM112 56l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 112l0 168 56 0 0-168-56 0zm336 0l0 168 56 0 0-168-56 0zM0 280l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM56 392l0 56 84 0 0 56 56 0 0-56 112 0 0 56 56 0 0-56 84 0 0-56-392 0zM196 504l0 56 112 0 0-56-112 0z"],
    "mobile": [392, 560, [128241, "mobile-android", "mobile-phone"], "f3ce", "M56 0l0 56 280 0 0-56-280 0zM0 56l0 448 56 0 0-448-56 0zm336 0l0 448 56 0 0-448-56 0zM112 112l0 56 168 0 0-56-168 0zm56 280l0 56 56 0 0-56-56 0zM56 504l0 56 280 0 0-56-280 0z"],
    "trophy": [616, 560, [127942], "f091", "M112 0l0 56-56 0 0 56 56 0 0 112-56 0 0 56 112 0 0-224 280 0 0 224 112 0 0-56-56 0 0-112 56 0 0-56-56 0 0-56-392 0zM0 112l0 112 56 0 0-112-56 0zm560 0l0 112 56 0 0-112-56 0zM168 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM224 336l0 56 56 0 0 112-84 0 0 56 224 0 0-56-84 0 0-112 56 0 0-56-168 0z"],
    "treasure-chest": [560, 560, [], "f723", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 392 560 0 0-392-56 0 0 112-196 0 0 112 56 0 0-56 140 0 0 168-448 0 0-168 140 0 0 56 56 0 0-112-196 0 0-112-56 0zm252 56l0 56 56 0 0-56-56 0zm0 168l0 56 56 0 0-56-56 0z"],
    "hand-point-up": [560, 560, [9757], "f0a6", "M168 0l0 392 56 0 0-392-56 0zM280 168l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zm112 56l0 280 56 0 0-280-56 0zM0 336l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 336 0 0-56-336 0z"],
    "camera-slash": [560, 560, [], "e0d9", "M0 0L0 56 56 56 56 0 0 0zM168 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 112l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0 224 56 0 0-280-112 0zM0 168l0 280 56 0 0-280-56 0zm168 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 448l0 56 280 0 0-56-280 0zm392 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "signal-bars-fair": [504, 560, ["signal-alt-2"], "f692", "M168 252l0 56 56 0 0-56-56 0zm-56 56l0 56-56 0 0 56-56 0 0 112 280 0 0-224-56 0 0 168-56 0 0-168-56 0zM56 420l56 0 0 56-56 0 0-56z"],
    "gift": [504, 560, [127873], "f06b", "M112 0l0 112-56 0 0 56-56 0 0 308 56 0 0 56 392 0 0-56 56 0 0-308-56 0 0-56-56 0 0-112-112 0 0 56-56 0 0-56-112 0zM56 168l168 0 0 112-168 0 0-112zm0 308l0-140 168 0 0 140-168 0zm392 0l-168 0 0-140 168 0 0 140zm0-308l0 112-168 0 0-112 168 0zM280 56l56 0 0 56-56 0 0-56zm-56 0l0 56-56 0 0-56 56 0z"],
    "gauge": [560, 560, ["dashboard", "gauge-med", "tachometer-alt-average"], "f624", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm196 0l0 56 56 0 0-56-56 0zm196 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM252 224l0 112-28 0 0 56 112 0 0-56-28 0 0-112-56 0zM112 252l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "signal": [504, 560, [128246, "signal-5", "signal-perfect"], "f012", "M420 28l0 504 56 0 0-504-56 0zM280 140l0 392 56 0 0-392-56 0zM140 252l0 280 56 0 0-280-56 0zM0 364l0 168 56 0 0-168-56 0z"],
    "share-nodes": [560, 560, ["share-alt"], "f1e0", "M392 0l0 56 112 0 0-56-112 0zM336 56l0 56-56 0 0 56 112 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM56 168l0 56 112 0 0-56-112 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zM0 224l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM56 336l0 56 112 0 0-56-112 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zM280 392l0 56 56 0 0 56 56 0 0-112-112 0zm224 0l0 112 56 0 0-112-56 0zM392 504l0 56 112 0 0-56-112 0z"],
    "percent": [560, 560, [62101, 62785, "percentage"], "25", "M56 0l0 56 112 0 0-56-112 0zM504 0l0 56 56 0 0-56-56 0zM0 56l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zm280 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 168l0 56 112 0 0-56-112 0zm280 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm224 0l0 56 112 0 0-56-112 0zM112 392l0 56 56 0 0-56-56 0zm224 0l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM56 448l0 56 56 0 0-56-56 0zM0 504l0 56 56 0 0-56-56 0zm392 0l0 56 112 0 0-56-112 0z"],
    "magnifying-glass": [560, 560, [128269, "search"], "f002", "M112 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 224 56 0 0-224-56 0zm392 0l0 224 56 0 0-224-56 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 56 224 0 0-56-224 0zm280 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "alien-8bit": [560, 560, [128126, "alien-monster"], "f8f6", "M112 28l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 84l0 56-56 0 0 56 336 0 0-56-56 0 0-56-56 0 0 56-112 0 0-56-56 0zM0 140l0 112 56 0 0 112 56 0 0-168-56 0 0-56-56 0zm504 0l0 56-56 0 0 168 56 0 0-112 56 0 0-112-56 0zM168 252l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM112 364l0 112 56 0 0-56 224 0 0 56 56 0 0-112-336 0zm56 112l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0z"],
    "palette": [560, 560, [127912], "f53f", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm168 0l0 56 56 0 0-56-56 0zm140 0l0 56 56 0 0-56-56 0zm196 0l0 112 56 0 0-112-56 0zM336 280l0 56 168 0 0-56-168 0zM168 308l0 56 56 0 0-56-56 0zm112 28l0 112 56 0 0-112-56 0zM56 392l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 504l0 56 168 0 0-56-168 0z"],
    "key": [560, 560, [128273], "f084", "M336 0l0 56 112 0 0-56-112 0zM280 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 112l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM280 224l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 280l0 56 56 0 0-56-56 0zm112 0l0 56 112 0 0-56-112 0zM168 336l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 448l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "skull": [560, 560, [128128], "f54c", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM140 196l0 112 112 0 0-56-56 0 0-56-56 0zm168 0l0 112 112 0 0-56-56 0 0-56-56 0zM56 392l0 112 56 0 0-112-56 0zm392 0l0 112 56 0 0-112-56 0zM196 448l0 56-84 0 0 56 336 0 0-56-84 0 0-56-56 0 0 56-56 0 0-56-56 0z"],
    "tree": [504, 560, [127794], "f1bb", "M224-28l0 56 56 0 0-56-56 0zM168 28l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 84l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 140l0 112 56 0 0 56 56 0 0-56 168 0 0 56 56 0 0-56 56 0 0-112-56 0 0 56-280 0 0-56-56 0zm0 168l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 364l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM56 420l0 56 168 0 0 112 56 0 0-112 168 0 0-56-392 0z"],
    "circle-half": [560, 560, [], "e110", "M168 0l0 56 112 0 0-56-112 0zM112 56l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 112 0 0-56-112 0z"],
    "flower": [616, 560, [127804, 10047], "f7ff", "M168 0l0 56 56 0 0-56-56 0zM280 0l0 56 56 0 0-56-56 0zM392 0l0 56 56 0 0-56-56 0zM112 56l0 168 56 0 0-168-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 168 56 0 0-168-56 0zM168 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 280l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0 168-224 0 0 56 504 0 0-56-224 0 0-168 56 0 0-56-168 0zm280 0l0 56 56 0 0-56-56 0zM0 336l0 168 56 0 0-168-56 0zm112 0l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zm112 0l0 168 56 0 0-168-56 0zM168 392l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "camera": [560, 560, [62258, "camera-alt"], "f030", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 336 56 0 0-280 56 0 0-56-112 0zm448 0l0 56 56 0 0 280 56 0 0-336-112 0zM224 168l0 56 112 0 0-56-112 0zm-56 56l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM224 336l0 56 112 0 0-56-112 0zM56 448l0 56 448 0 0-56-448 0z"],
    "eye": [560, 560, [128065], "f06e", "M168 56l0 56 224 0 0-56-224 0zm-56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 168l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM252 196l0 56-56 0 0 112 168 0 0-168-112 0zM0 224l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM56 336l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 448l0 56 224 0 0-56-224 0z"],
    "caret-left": [336, 560, [], "f0d9", "M224 28l0 56-56 0 0 56 56 0 0 280-56 0 0 56 56 0 0 56 56 0 0-504-56 0zM112 140l0 56 56 0 0-56-56 0zM56 196l0 56 56 0 0-56-56 0zM0 252l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "trash": [504, 560, [], "f1f8", "M392 560l-280 0 0-56 280 0 0 56zm0-448l112 0 0 56-56 0 0 336-56 0 0-336-280 0 0 336-56 0 0-336-56 0 0-56 112 0 0-112 280 0 0 112zM224 420l-56 0 0-168 56 0 0 168zm112 0l-56 0 0-168 56 0 0 168zM168 112l168 0 0-56-168 0 0 56z"],
    "equals": [504, 560, [62764], "3d", "M0 140l0 56 504 0 0-56-504 0zM0 364l0 56 504 0 0-56-504 0z"],
    "expand": [504, 560, [], "f065", "M0 28l0 196 56 0 0-140 140 0 0-56-196 0zm308 0l0 56 140 0 0 140 56 0 0-196-196 0zM0 336l0 196 196 0 0-56-140 0 0-140-56 0zm448 0l0 140-140 0 0 56 196 0 0-196-56 0z"],
    "stop": [504, 560, [9209], "f04d", "M56 28l0 56 392 0 0-56-392 0zM0 84l0 392 56 0 0-392-56 0zm448 0l0 392 56 0 0-392-56 0zM56 476l0 56 392 0 0-56-392 0z"],
    "command": [504, 560, [], "e142", "M56 28l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM0 84l0 112 56 0 0-112-56 0zm168 0l0 112-112 0 0 56 112 0 0 56-112 0 0 56 112 0 0 112 56 0 0-112 56 0 0 112 56 0 0-112 112 0 0-56-112 0 0-56 112 0 0-56-112 0 0-112-56 0 0 112-56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM224 308l0-56 56 0 0 56-56 0zM0 364l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM56 476l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0z"],
    "tv-retro": [504, 560, [128250], "f401", "M448 532l-392 0 0-56 392 0 0 56zM56 476l-56 0 0-336 56 0 0 336zm448 0l-56 0 0-336 56 0 0 336zM392 420l-280 0 0-224 280 0 0 224zM168 364l168 0 0-112-168 0 0 112zM224 84l56 0 0-56 56 0 0 56 112 0 0 56-392 0 0-56 112 0 0-56 56 0 0 56zM168 28l-56 0 0-56 56 0 0 56zm224 0l-56 0 0-56 56 0 0 56z"],
    "angle-left": [336, 560, [8249], "f104", "M224 28l0 56 56 0 0-56-56 0zM168 84l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 196l0 56 56 0 0-56-56 0zM0 252l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "game-console-handheld": [448, 560, [], "f8bb", "M392 560l-336 0 0-56 336 0 0 56zM56 504l-56 0 0-448 56 0 0 448zm392 0l-56 0 0-448 56 0 0 448zM336 448l-56 0 0-56 56 0 0 56zM168 420l-56 0 0-56 56 0 0 56zm112-28l-56 0 0-56 56 0 0 56zm56-280l0 168-224 0 0-168 224 0zM168 168l0 56 112 0 0-56-112 0zM392 56l-336 0 0-56 336 0 0 56z"],
    "clock": [560, 560, [128339, "clock-four"], "f017", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM252 140l0 168 56 0 0-168-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM308 308l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "book": [448, 560, [128212], "f02d", "M56 0l0 56 336 0 0 336-280 0 0 56 336 0 0-448-392 0zM0 56l0 448 56 0 0-448-56 0zM168 168l0 56 168 0 0-56-168 0zM56 504l0 56 392 0 0-56-392 0z"],
    "rocket": [616, 560, [], "f135", "M280-28l0 56 56 0 0-56-56 0zM224 28l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM168 84l0 168-56 0 0 56 56 0 0 224 56 0 0-56 168 0 0 56 56 0 0-224 56 0 0-56-56 0 0-168-56 0 0 336-168 0 0-336-56 0zm112 56l0 56 56 0 0-56-56 0zM56 308l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM0 364l0 224 168 0 0-56-112 0 0-168-56 0zm560 0l0 168-112 0 0 56 168 0 0-224-56 0z"],
    "paper-plane": [560, 560, [61913], "f1d8", "M0 28l0 112 56 0 0-56 112 0 0-56-168 0zM168 84l0 56 112 0 0-56-112 0zM56 140l0 112 56 0 0-112-56 0zm224 0l0 56 112 0 0-56-112 0zm112 56l0 56 112 0 0-56-112 0zM112 252l0 56 168 0 0-56-168 0zm392 0l0 56 56 0 0-56-56 0zM56 308l0 112 56 0 0-112-56 0zm336 0l0 56 112 0 0-56-112 0zM280 364l0 56 112 0 0-56-112 0zM0 420l0 112 168 0 0-56-112 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0z"],
    "heart": [616, 560, [128153, 128154, 128155, 128156, 128420, 129293, 129294, 129505, 9829, 10084, 61578], "f004", "M112 0l0 56 112 0 0-56-112 0zM392 0l0 56 112 0 0-56-112 0zM56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM0 112l0 168 56 0 0-168-56 0zm280 0l0 56 56 0 0-56-56 0zm280 0l0 168 56 0 0-168-56 0zM56 280l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM112 336l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM168 392l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM224 448l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "battery-half": [672, 560, ["battery-3"], "f242", "M112 84l0 56 392 0 0-56-392 0zM56 140l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zM168 196l0 168 56 0 0-168-56 0zm112 0l0 168 56 0 0-168-56 0zm336 0l0 168 56 0 0-168-56 0zM112 420l0 56 392 0 0-56-392 0z"],
    "font-awesome": [504, 560, [62501, 62694, "font-awesome-flag", "font-awesome-logo-full"], "f2b4", "M84 56l420 0 0 112-56 0 0-56-364 0 0 280 364 0 0-56 56 0 0 112-420 0 0 112-56 0 0-560 56 0 0 56zM448 336l-56 0 0-56 56 0 0 56zm-56-56l-56 0 0-56 56 0 0 56zm56-56l-56 0 0-56 56 0 0 56z"],
    "clone": [560, 560, [], "f24d", "M224 0l0 56 280 0 0-56-280 0zM168 56l0 56 56 0 0-56-56 0zm336 0l0 280 56 0 0-280-56 0zM56 168l0 56 280 0 0-56-280 0zM0 224l0 280 56 0 0-280-56 0zm336 0l0 280 56 0 0-280-56 0zM448 336l0 56 56 0 0-56-56 0zM56 504l0 56 280 0 0-56-280 0z"],
    "forward": [560, 560, [9193], "f04e", "M0 56l0 448 112 0 0-56-56 0 0-336 56 0 0-56-112 0zm280 0l0 448 112 0 0-56-56 0 0-336 56 0 0-56-112 0zM112 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 168l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zm56 56l0 112 56 0 0-112-56 0zM168 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0z"],
    "triangle": [560, 560, [9650], "f2ec", "M224 0l0 56 112 0 0-56-112 0zM168 56l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM112 168l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM56 280l0 112 56 0 0-112-56 0zm392 0l0 112 56 0 0-112-56 0zM0 392l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM56 504l0 56 448 0 0-56-448 0z"],
    "arrow-down-to-bracket": [504, 560, [], "e094", "M224-28l0 308-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-308-56 0zM56 168l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 392l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM56 504l0 56 392 0 0-56-392 0z"],
    "star-half": [672, 560, [61731], "f089", "M308-56l0 112 56 0 0-112-56 0zM252 56l0 112 56 0 0-112-56 0zM0 168l0 56 56 0 0 56 56 0 0-56 140 0 0-56-252 0zM112 280l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm-56 56l0 168 112 0 0-56-56 0 0-112-56 0zm168 0l0 56 84 0 0-56-84 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "arrow-down-to-line": [448, 560, ["arrow-to-bottom"], "f33d", "M196 0l0 336-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-336-56 0zM28 224l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM84 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 504l0 56 448 0 0-56-448 0z"],
    "fire": [504, 560, [128293], "f06d", "M224-56l0 56-56 0 0 56 56 0 0 112 56 0 0-224-56 0zM112 56l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm280 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm56 56l0 168 56 0 0-168-56 0zM168 252l0 112 56 0 0-112-56 0zm112 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 504l0 56 168 0 0-56-168 0z"],
    "rectangle-vertical": [448, 560, ["rectangle-portrait"], "f2fb", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 448 56 0 0-448-56 0zm392 0l0 448 56 0 0-448-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "burger": [560, 560, ["hamburger"], "f805", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 112 448 0 0-112-56 0 0 56-336 0 0-56-56 0zM0 280l0 56 560 0 0-56-560 0zM56 392l0 112 56 0 0-56 336 0 0 56 56 0 0-112-448 0zm56 112l0 56 336 0 0-56-336 0z"],
    "print": [560, 560, [128424, 128438, 9113], "f02f", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 84-56 0 0 56 448 0 0-56-56 0 0-84-56 0 0 84-224 0 0-84-56 0zM0 196l0 224 112 0 0 84 56 0 0-140 224 0 0 140 56 0 0-84 112 0 0-224-56 0 0 168-56 0 0-56-336 0 0 56-56 0 0-168-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "users": [616, 560, [], "f0c0", "M112 56l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM56 112l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM112 224l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM56 336l0 56 224 0 0-56-224 0zm336 0l0 56 168 0 0-56-168 0zM0 392l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0z"],
    "alarm-clock": [616, 560, [9200], "f34e", "M112-28l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM56 28l0 56 56 0 0-56-56 0zm168 0l0 56 168 0 0-56-168 0zm280 0l0 56 56 0 0-56-56 0zM0 84l0 112 56 0 0-112-56 0zm168 0l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zM112 140l0 56 56 0 0-56-56 0zm168 0l0 168 56 0 0-168-56 0zm168 0l0 56 56 0 0-56-56 0zM56 196l0 168 56 0 0-168-56 0zm448 0l0 168 56 0 0-168-56 0zM336 308l0 56 56 0 0-56-56 0zM112 364l0 112 112 0 0-56-56 0 0-56-56 0zm336 0l0 56-56 0 0 56 112 0 0-112-56 0zM56 476l0 56 56 0 0-56-56 0zm168 0l0 56 168 0 0-56-168 0zm280 0l0 56 56 0 0-56-56 0z"],
    "database": [448, 560, [], "f1c0", "M112 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 112 56 0 0-112-56 0zm392 0l0 112 56 0 0-112-56 0zM56 224l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 280l0 56 56 0 0-56-56 0zm112 0l0 56 224 0 0-56-224 0zm280 0l0 56 56 0 0-56-56 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 392l0 56 56 0 0-56-56 0zm112 0l0 56 224 0 0-56-224 0zm280 0l0 56 56 0 0-56-56 0zM56 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 504l0 56 224 0 0-56-224 0z"],
    "newspaper": [560, 560, [128240], "f1ea", "M112 56l0 336 56 0 0-280 336 0 0 336 56 0 0-392-448 0zM0 140l0 308 56 0 0-308-56 0zm224 28l0 56 224 0 0-56-224 0zm0 112l0 56 224 0 0-56-224 0zM56 448l0 56 448 0 0-56-448 0z"],
    "location-arrow": [560, 560, [], "f124", "M392 0l0 56 112 0 0 112 56 0 0-168-168 0zM280 56l0 56 112 0 0-56-112 0zM168 112l0 56 112 0 0-56-112 0zM56 168l0 56 112 0 0-56-112 0zm392 0l0 112 56 0 0-112-56 0zM0 224l0 112 56 0 0-112-56 0zm392 56l0 112 56 0 0-112-56 0zM56 336l0 56 112 0 0 112 56 0 0-168-168 0zm280 56l0 112 56 0 0-112-56 0zM224 504l0 56 112 0 0-56-112 0z"],
    "arrow-right-to-bracket": [616, 560, ["sign-in"], "f090", "M420 28l0 56 112 0 0-56-112 0zM196 84l0 56 56 0 0-56-56 0zm336 0l0 392 56 0 0-392-56 0zM252 140l0 56 56 0 0-56-56 0zm56 56l0 56-308 0 0 56 308 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zM252 364l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm224 56l0 56 112 0 0-56-112 0z"],
    "video-slash": [560, 560, [], "f4e2", "M0 0L0 56 56 56 56 0 0 0zM56 56l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zM112 112l0 56 56 0 0-56-56 0zm224 0l0 112 112 0 0-56-56 0 0-56-56 0zm112 0l0 56 56 0 0 224 56 0 0-280-112 0zM0 168l0 280 56 0 0-280-56 0zm168 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 448l0 56 280 0 0-56-280 0zm392 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "comment": [560, 560, [128489, 61669], "f075", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 280 56 0 0-280-56 0zm504 0l0 280 56 0 0-280-56 0zM56 392l0 56 140 0 0 112 112 0 0-56-56 0 0-112-196 0zm308 0l0 56 140 0 0-56-140 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "video": [560, 560, ["video-camera"], "f03d", "M56 56l0 56 280 0 0-56-280 0zM0 112l0 336 56 0 0-336-56 0zm336 0l0 336 56 0 0-56 56 0 0-56-56 0 0-112 56 0 0-56-56 0 0-56-56 0zm112 0l0 56 56 0 0 224-56 0 0 56 112 0 0-336-112 0zM56 448l0 56 280 0 0-56-280 0z"],
    "clipboard": [448, 560, [128203], "f328", "M168-28l0 56 112 0 0-56-112 0zM112 28l0 56-56 0 0 56 56 0 0 56 224 0 0-56 56 0 0-56-56 0 0-56-56 0 0 112-112 0 0-112-56 0zM0 140l0 364 56 0 0-364-56 0zm392 0l0 364 56 0 0-364-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "laptop": [560, 560, [128187], "f109", "M112 28l0 56 336 0 0-56-336 0zM56 84l0 336 56 0 0-56 336 0 0 56 56 0 0-336-56 0 0 224-336 0 0-224-56 0zM0 420l0 112 560 0 0-112-56 0 0 56-448 0 0-56-56 0z"],
    "joystick": [448, 560, [128377], "f8c5", "M140 0l0 56 168 0 0-56-168 0zM84 56l0 168 56 0 0-168-56 0zm224 0l0 168 56 0 0-168-56 0zM140 224l0 56 56 0 0 56-140 0 0 56 140 0 0 56 56 0 0-56 140 0 0-56-140 0 0-56 56 0 0-56-168 0zM0 392l0 168 448 0 0-168-56 0 0 112-336 0 0-112-56 0z"],
    "thumbs-down": [560, 560, [128078, 61576], "f165", "M168 84l280 0 0-56-448 0 0 280 168 0 0-224zM56 84l56 0 0 168-56 0 0-168zm448 0l-56 0 0 112 56 0 0-112zM392 364l168 0 0-168-56 0 0 112-168 0 0 168 56 0 0-112zM224 308l-56 0 0 112 56 0 0-112zM336 532l0-56-56 0 0-56-56 0 0 112 112 0z"],
    "utensils": [504, 560, [127860, 61685, "cutlery"], "f2e7", "M0 28l0 168 56 0 0-168-56 0zm112 0l0 168-56 0 0 56 56 0 0 280 56 0 0-280 56 0 0-56-56 0 0-168-56 0zm112 0l0 168 56 0 0-168-56 0zm168 0l0 56-56 0 0 280 112 0 0 168 56 0 0-504-112 0zm0 56l56 0 0 224-56 0 0-224z"],
    "circle-xmark": [560, 560, [61532, "times-circle", "xmark-circle"], "f057", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM196 196l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "microphone": [448, 560, [], "f130", "M168 0l0 56 112 0 0-56-112 0zM112 56l0 224 56 0 0-224-56 0zm168 0l0 224 56 0 0-224-56 0zM0 224l0 112 56 0 0-112-56 0zm392 0l0 112 56 0 0-112-56 0zM168 280l0 56 112 0 0-56-112 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 56 84 0 0 56-84 0 0 56 224 0 0-56-84 0 0-56 84 0 0-56-224 0z"],
    "crown": [616, 560, [128081], "f521", "M280-28l0 56 56 0 0-56-56 0zM224 28l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM0 84l0 224 56 0 0-168 56 0 0-56-112 0zm168 0l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0 168 56 0 0-224-112 0zM112 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM280 224l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 308l0 168 56 0 0-168-56 0zm448 0l0 168 56 0 0-168-56 0zM280 336l0 56 56 0 0-56-56 0zM112 476l0 56 392 0 0-56-392 0z"],
    "arrow-up-wide-short": [560, 560, ["sort-amount-up"], "f161", "M112 28l0 56-56 0 0 56 56 0 0 392 56 0 0-392 56 0 0-56-56 0 0-56-56 0zM336 84l0 56 224 0 0-56-224 0zM0 140l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm112 56l0 56 168 0 0-56-168 0zm0 112l0 56 112 0 0-56-112 0zm0 112l0 56 56 0 0-56-56 0z"],
    "sun": [560, 560, [9728], "f185", "M252 0l0 56 56 0 0-56-56 0zM56 56l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM224 112l0 56 112 0 0-56-112 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM112 224l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM0 252l0 56 56 0 0-56-56 0zm504 0l0 56 56 0 0-56-56 0zM168 336l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 392l0 56 112 0 0-56-112 0zM56 448l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM252 504l0 56 56 0 0-56-56 0z"],
    "image": [504, 560, [], "f03e", "M56 28l0 56 392 0 0-56-392 0zM0 84l0 392 56 0 0-392-56 0zm448 0l0 224-56 0 0 56 56 0 0 112 56 0 0-392-56 0zM140 140l0 28-28 0 0 56 28 0 0 28 56 0 0-28 28 0 0-56-28 0 0-28-56 0zM336 252l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56-112 0 0 56 392 0 0-56-224 0 0-56-56 0z"],
    "notdef": [448, 560, [], "e1fe", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 448 56 0 0-56 56 0 0-56-56 0 0-224 56 0 0-56-56 0 0-56-56 0zm392 0l0 56-56 0 0 56 56 0 0 224-56 0 0 56 56 0 0 56 56 0 0-448-56 0zM112 168l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 224l0 112 112 0 0-112-112 0zM112 336l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "pencil": [504, 560, [9999, 61504, "pencil-alt"], "f303", "M336 28l0 56 112 0 0-56-112 0zM280 84l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zM224 140l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM168 196l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 252l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 308l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 364l0 168 168 0 0-56-56 0 0-56-56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "battery-low": [672, 560, ["battery-1"], "e0b1", "M112 84l0 56 392 0 0-56-392 0zM56 140l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zM168 196l0 168 56 0 0-168-56 0zm448 0l0 168 56 0 0-168-56 0zM112 420l0 56 392 0 0-56-392 0z"],
    "coin-front": [560, 560, [], "e3fc", "M392 560l-224 0 0-56 224 0 0 56zM168 504l-56 0 0-56 56 0 0 56zm280 0l-56 0 0-56 56 0 0 56zM112 448l-56 0 0-56 56 0 0 56zm392 0l-56 0 0-56 56 0 0 56zM56 392l-56 0 0-224 56 0 0 224zm252 0l-56 0 0-224 56 0 0 224zm252 0l-56 0 0-224 56 0 0 224zM112 168l-56 0 0-56 56 0 0 56zm392 0l-56 0 0-56 56 0 0 56zM168 112l-56 0 0-56 56 0 0 56zm280 0l-56 0 0-56 56 0 0 56zM392 56l-224 0 0-56 224 0 0 56z"],
    "lightbulb": [448, 560, [128161], "f0eb", "M112 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 224 56 0 0-224-56 0zm392 0l0 224 56 0 0-224-56 0zM140 224l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 112-84 0 0 112 56 0 0-56 112 0 0 56 56 0 0-112-84 0 0-112-56 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 112 0 0-56-112 0z"],
    "block-question": [504, 560, [], "e3dd", "M448 84l-392 0 0 392 392 0 0-392 56 0 0 392-56 0 0 56-392 0 0-56-56 0 0-392 56 0 0-56 392 0 0 56zM280 420l-56 0 0-56 56 0 0 56zm0-112l-56 0 0-56 56 0 0 56zm56-56l-56 0 0-56 56 0 0 56zm-56-56l-112 0 0-56 112 0 0 56z"],
    "code": [616, 560, [], "f121", "M336 28l0 168 56 0 0-168-56 0zM112 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM56 196l0 56 56 0 0-56-56 0zm224 0l0 168 56 0 0-168-56 0zm224 0l0 56 56 0 0-56-56 0zM0 252l0 56 56 0 0-56-56 0zm560 0l0 56 56 0 0-56-56 0zM56 308l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM112 364l0 56 56 0 0-56-56 0zm112 0l0 168 56 0 0-168-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "address-card": [560, 560, [62140, "contact-card", "vcard"], "f2bb", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 336 56 0 0-336-56 0zm504 0l0 336 56 0 0-336-56 0zM168 168l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zM112 224l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zM112 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 448l0 56 448 0 0-56-448 0z"],
    "angle-right": [336, 560, [8250], "f105", "M56 28l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 476l0 56 56 0 0-56-56 0z"],
    "face-meh": [560, 560, [128528, "meh"], "f11a", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM168 196l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 308l0 56 224 0 0-56-224 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "map": [560, 560, [128506, 62072], "f279", "M112 56l0 56 56 0 0 280-56 0 0 56 168 0 0-56-56 0 0-280 56 0 0-56-168 0zm336 0l0 56 56 0 0 280-56 0 0 56 112 0 0-392-112 0zM0 112l0 392 112 0 0-56-56 0 0-280 56 0 0-56-112 0zm280 0l0 56 56 0 0 280-56 0 0 56 168 0 0-56-56 0 0-280 56 0 0-56-168 0z"],
    "plus-large": [560, 560, [], "e59e", "M224 0l0 56 112 0 0-56-112 0zM168 56l0 112-112 0 0 56 168 0 0-56 0-112-56 0zm168 0l0 168 168 0 0-56-112 0 0-112-56 0zM0 224l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM56 336l0 56 112 0 0 112 56 0 0-112 0-56-168 0zm280 0l0 168 56 0 0-112 112 0 0-56-168 0zM224 504l0 56 112 0 0-56-112 0z"],
    "thumbtack": [504, 560, [128204, 128392, "thumb-tack"], "f08d", "M56 0l0 56 56 0 0 140 56 0 0-140 168 0 0 140 56 0 0-140 56 0 0-56-392 0zm0 196l0 140-56 0 0 56 224 0 0 168 56 0 0-168 224 0 0-56-56 0 0-140-56 0 0 140-280 0 0-140-56 0z"],
    "hand-point-down": [560, 560, [], "f0a7", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 280 56 0 0-280-56 0zm392 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM336 168l0 392 56 0 0-392-56 0zm168 0l0 56 56 0 0-56-56 0zM112 280l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0z"],
    "money-bill-wave": [560, 560, [], "f53a", "M112 56l0 56 168 0 0-56-168 0zm336 0l0 56 56 0 0 112-56 0 0 56 56 0 0 112-56 0 0 56 112 0 0-392-112 0zM0 112l0 392 112 0 0-56-56 0 0-112 56 0 0-56-56 0 0-112 56 0 0-56-112 0zm280 0l0 56 168 0 0-56-168 0zM252 224l0 112 56 0 0-112-56 0zM112 392l0 56 168 0 0-56-168 0zm168 56l0 56 168 0 0-56-168 0z"],
    "folder": [560, 560, [128193, 128447, 61716, "folder-blank"], "f07b", "M56 28l0 56 168 0 0-56-168 0zM0 84l0 364 56 0 0-364-56 0zm224 0l0 56 56 0 0-56-56 0zm56 56l0 56 224 0 0 252 56 0 0-308-280 0zM56 448l0 56 448 0 0-56-448 0z"],
    "arrow-up-from-bracket": [504, 560, [], "e09a", "M224-28l0 56-56 0 0 56 56 0 0 308 56 0 0-308 56 0 0-56-56 0 0-56-56 0zM112 84l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 392l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM56 504l0 56 392 0 0-56-392 0z"],
    "cloud": [672, 560, [9729], "f0c2", "M336 28l0 56 224 0 0-56-224 0zM280 84l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 140l0 56 168 0 0-56-168 0zm504 0l0 280 56 0 0-280-56 0zM56 196l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 252l0 168 56 0 0-168-56 0zm336 0l0 56 56 0 0-56-56 0zM56 420l0 56 56 0 0-56-56 0zm504 0l0 56 56 0 0-56-56 0zM112 476l0 56 448 0 0-56-448 0z"],
    "circle": [560, 560, [128308, 128309, 128992, 128993, 128994, 128995, 128996, 9679, 9898, 9899, 11044, 61708, 61915], "f111", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "backward": [560, 560, [9194], "f04a", "M168 56l0 56 56 0 0 336-56 0 0 56 112 0 0-448-112 0zm280 0l0 56 56 0 0 336-56 0 0 56 112 0 0-448-112 0zM112 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 168l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 224l0 112 56 0 0-112-56 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0z"],
    "link": [560, 560, [128279, "chain"], "f0c1", "M280 0l0 56 168 0 0-56-168 0zM224 56l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm56 56l0 168 56 0 0-168-56 0zM336 168l0 56 56 0 0-56-56 0zM56 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 280l0 168 56 0 0-168-56 0zm224 0l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 336l0 56 56 0 0-56-56 0zM56 448l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 504l0 56 168 0 0-56-168 0z"],
    "backward-step": [504, 560, ["step-backward"], "f048", "M56 28l0 56 112 0 0-56-112 0zm336 0l0 56 56 0 0 392-56 0 0 56 112 0 0-504-112 0zM0 84l0 392 56 0 0-392-56 0zm168 0l0 392 56 0 0-112 56 0 0-56-56 0 0-56 56 0 0-56-56 0 0-112-56 0zm168 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm0 224l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 476l0 56 112 0 0-56-112 0z"],
    "circle-user": [560, 560, [62142, "user-circle"], "f2bd", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zm224 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm168 0l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zm168 0l0 224 56 0 0-224-56 0zM224 280l0 56 112 0 0-56-112 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM112 392l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "hand": [560, 560, [129306, 9995, "hand-paper"], "f256", "M280 0l0 280 56 0 0-280-56 0zM168 56l0 336 56 0 0-336-56 0zm224 0l0 224 56 0 0-224-56 0zm112 56l0 392 56 0 0-392-56 0zM0 336l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 336 0 0-56-336 0z"],
    "coin-vertical": [560, 560, [], "e3fd", "M168 0l0 56 112 0 0 56 56 0 0-56 56 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm196 0l0 224 56 0 0-224-56 0zm196 0l0 224 56 0 0-224-56 0zm112 0l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm168 0l0 56-112 0 0 56 224 0 0-56-56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0z"],
    "thumbs-up": [560, 560, [128077, 61575], "f164", "M168 476l280 0 0 56-448 0 0-280 168 0 0 224zM56 476l56 0 0-168-56 0 0 168zm448 0l-56 0 0-112 56 0 0 112zM392 196l168 0 0 168-56 0 0-112-168 0 0-168 56 0 0 112zM224 252l-56 0 0-112 56 0 0 112zM336 28l0 56-56 0 0 56-56 0 0-112 112 0z"],
    "building": [448, 560, [127970, 61687], "f1ad", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 504 448 0 0-504-56 0 0 448-140 0 0-56-56 0 0 56-140 0 0-448-56 0zm140 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM140 224l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM140 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0z"],
    "gear": [616, 560, [9881, "cog"], "f013", "M280-28l0 112-56 0 0 56 168 0 0-56-56 0 0-112-56 0zM56 28l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM112 84l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM168 140l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 196l0 56-112 0 0 56 112 0 0 56 56 0 0-168-56 0zm336 0l0 168 56 0 0-56 112 0 0-56-112 0 0-56-56 0zM280 252l0 56 56 0 0-56-56 0zM168 364l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 420l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0 112 56 0 0-112 56 0 0-56-168 0zm224 0l0 56 56 0 0-56-56 0zM56 476l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0z"],
    "circle-question": [560, 560, [62108, "question-circle"], "f059", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zm224 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zm168 0l0 224 56 0 0-224-56 0zM252 280l0 56 84 0 0-56-84 0zM56 392l0 56 56 0 0-56-56 0zm196 0l0 56 56 0 0-56-56 0zm196 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "file": [448, 560, [128196, 128459, 61462], "f15b", "M56 0l0 56 168 0 0 168 168 0 0 280 56 0 0-392-56 0 0 56-112 0 0-112 56 0 0-56-280 0zM0 56l0 448 56 0 0-448-56 0zm336 0l0 56 56 0 0-56-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "volume-slash": [560, 560, [128263], "f2e2", "M0 0L0 56 56 56 56 0 0 0zM224 28l0 56 56 0 0 84 56 0 0-140-112 0zM56 56l0 56 56 0 0-56-56 0zM448 84l0 56 56 0 0-56-56 0zM112 112l0 56 56 0 0-56-56 0zm392 28l0 252 56 0 0-252-56 0zM0 168l0 196 56 0 0-196-56 0zm168 0l0 56 56 0 0-56-56 0zm224 28l0 84 56 0 0-84-56 0zM224 224l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 364l0 56 112 0 0-56-112 0zm336 28l0 56 56 0 0-56-56 0zM168 420l0 56 56 0 0-56-56 0zm280 28l0 56 56 0 0-56-56 0zM224 476l0 56 112 0 0-56-112 0zm280 28l0 56 56 0 0-56-56 0z"],
    "hand-point-right": [560, 560, [], "f0a4", "M168 0l0 56 56 0 0-56-56 0zM112 56l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zM0 168l0 336 56 0 0-336-56 0zm168 0l0 56 392 0 0-56-392 0zM280 280l0 56 112 0 0-56-112 0zm0 112l0 56 112 0 0-56-112 0zM56 504l0 56 280 0 0-56-280 0z"],
    "arrow-up": [504, 560, [8593], "f062", "M224-28l0 56-56 0 0 56 56 0 0 504 56 0 0-504 56 0 0-56-56 0 0-56-56 0zM112 84l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 196l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0z"],
    "rectangle": [560, 560, [9644, "rectangle-landscape"], "f2fa", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 336 56 0 0-336-56 0zm504 0l0 336 56 0 0-336-56 0zM56 448l0 56 448 0 0-56-448 0z"],
    "grid": [504, 560, ["grid-3"], "e195", "M56 28l0 56-56 0 0 392 56 0 0 56 392 0 0-56 56 0 0-392-56 0 0-56-392 0zm0 56l168 0 0 168-168 0 0-168zm0 392l0-168 168 0 0 168-168 0zm392 0l-168 0 0-168 168 0 0 168zm0-392l0 168-168 0 0-168 168 0z"],
    "shirt": [560, 560, [128085, "t-shirt", "tshirt"], "f553", "M112 0l0 56 112 0 0-56-112 0zM336 0l0 56 112 0 0-56-112 0zM0 56l0 224 112 0 0 224 56 0 0-336-56 0 0 56-56 0 0-112 56 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zm224 0l0 56 56 0 0 112-56 0 0-56-56 0 0 336 56 0 0-224 112 0 0-224-112 0zM168 504l0 56 224 0 0-56-224 0z"],
    "suitcase": [560, 560, [129523], "f0f2", "M196 0l0 56 168 0 0-56-168 0zM140 56l0 56-84 0 0 56 84 0 0 308-84 0 0 56 448 0 0-56-84 0 0-308 84 0 0-56-84 0 0-56-56 0 0 56-168 0 0-56-56 0zM0 168l0 308 56 0 0-308-56 0zM196 476l0-308 168 0 0 308-168 0zM504 168l0 308 56 0 0-308-56 0z"],
    "play": [560, 560, [9654], "f04b", "M56 28l0 504 112 0 0-56-56 0 0-392 56 0 0-56-112 0zM168 84l0 56 112 0 0-56-112 0zm112 56l0 56 112 0 0-56-112 0zm112 56l0 56 112 0 0-56-112 0zm112 56l0 56 56 0 0-56-56 0zM392 308l0 56 112 0 0-56-112 0zM280 364l0 56 112 0 0-56-112 0zM168 420l0 56 112 0 0-56-112 0z"],
    "paperclip": [392, 560, [128206], "f0c6", "M168-28l0 56 112 0 0-56-112 0zM112 28l0 392 56 0 0-392-56 0zm168 0l0 56 56 0 0-56-56 0zm56 56l0 392 56 0 0-392-56 0zM0 140l0 336 56 0 0-336-56 0zm224 0l0 280 56 0 0-280-56 0zM168 420l0 56 56 0 0-56-56 0zM56 476l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 532l0 56 168 0 0-56-168 0z"],
    "signal-bars-weak": [504, 560, ["signal-alt-1"], "f691", "M56 364l0 56 56 0 0-56-56 0zM0 420l0 112 168 0 0-112-56 0 0 56-56 0 0-56-56 0z"],
    "wrench": [560, 560, [128295], "f0ad", "M112 0l0 56 56 0 0 168 56 0 0-224-112 0zM336 0l0 224 56 0 0-168 56 0 0-56-112 0zM56 56l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 112l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM224 224l0 56 112 0 0-56-112 0zM56 336l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 448l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0z"],
    "life-ring": [560, 560, [], "f1cd", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56-56 0 0 56 112 0 0-112-56 0zm280 0l0 112 112 0 0-56-56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm168 0l0 224 224 0 0-224-224 0zm336 0l0 224 56 0 0-224-56 0zM224 336l0-112 112 0 0 112-112 0zM56 392l0 56 56 0 0 56 56 0 0-112-112 0zm336 0l0 112 56 0 0-56 56 0 0-56-112 0zM168 504l0 56 224 0 0-56-224 0z"],
    "calendar": [504, 560, [128197, 128198], "f133", "M112 0l0 56-56 0 0 56 392 0 0-56-56 0 0-56-56 0 0 56-168 0 0-56-56 0zM0 112l0 364 56 0 0-196 392 0 0 196 56 0 0-364-56 0 0 112-392 0 0-112-56 0zM56 476l0 56 392 0 0-56-392 0z"],
    "check": [560, 560, [10003, 10004], "f00c", "M504 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM0 336l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "wifi-slash": [616, 560, [], "f6ac", "M28 0l0 56 56 0 0-56-56 0zM224 28l0 56 168 0 0-56-168 0zM84 56l0 56 56 0 0-56-56 0zM392 84l0 56 112 0 0-56-112 0zM140 112l0 56 56 0 0-56-56 0zm364 28l0 56 56 0 0-56-56 0zM196 168l0 56 56 0 0-56-56 0zM0 196l0 56 56 0 0-56-56 0zm560 0l0 56 56 0 0-56-56 0zM252 224l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM112 308l0 56 56 0 0-56-56 0zm252 28l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM224 420l0 56 56 0 0-56-56 0zm252 28l0 56 56 0 0-56-56 0zM280 476l0 56 56 0 0-56-56 0zm252 28l0 56 56 0 0-56-56 0z"],
    "chart-bar": [560, 560, ["bar-chart"], "f080", "M224 0l0 56 112 0 0-56-112 0zM168 56l0 168-112 0 0 56-56 0 0 224 560 0 0-336-56 0 0-56-112 0 0-56-56 0 0 392-112 0 0-392-56 0zM56 280l112 0 0 168-112 0 0-168zM504 168l0 280-112 0 0-280 112 0z"],
    "house": [616, 560, [127968, 63498, 63500, "home", "home-alt", "home-lg-alt"], "f015", "M392 308l0 168 112 0 0 56-392 0 0-56 112 0 0-168 168 0zM112 476l-56 0 0-168-56 0 0-56 56 0 0-56 56 0 0 280zM280 364l0 112 56 0 0-112-56 0zM560 252l56 0 0 56-56 0 0 168-56 0 0-280 56 0 0 56zM168 196l-56 0 0-56 56 0 0 56zm336 0l-56 0 0-56 56 0 0 56zM224 140l-56 0 0-56 56 0 0 56zm224 0l-56 0 0-56 56 0 0 56zM392 84l-168 0 0-56 168 0 0 56z"],
    "play-pause": [560, 560, [], "e22f", "M0 56l0 448 112 0 0-56-56 0 0-336 56 0 0-56-112 0zm280 0l0 56 84 0 0-56-84 0zm140 0l0 56 84 0 0-56-84 0zM112 112l0 56 56 0 0-56-56 0zm112 0l0 56-56 0 0 56 56 0 0 112-56 0 0 56 56 0 0 56 56 0 0-336-56 0zm140 0l0 336 56 0 0-336-56 0zm140 0l0 336 56 0 0-336-56 0zM112 392l0 56 56 0 0-56-56 0zm168 56l0 56 84 0 0-56-84 0zm140 0l0 56 84 0 0-56-84 0z"],
    "sliders": [504, 560, ["sliders-h"], "f1de", "M364 28l0 56-364 0 0 56 364 0 0 56 56 0 0-56 84 0 0-56-84 0 0-56-56 0zM112 196l0 56-112 0 0 56 112 0 0 56 56 0 0-56 336 0 0-56-336 0 0-56-56 0zM280 364l0 56-280 0 0 56 280 0 0 56 56 0 0-56 168 0 0-56-168 0 0-56-56 0z"],
    "face-frown": [560, 560, [9785, "frown"], "f119", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM168 196l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 308l0 56 112 0 0-56-112 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "forward-step": [504, 560, ["step-forward"], "f051", "M0 28l0 504 112 0 0-56-56 0 0-392 56 0 0-56-112 0zm336 0l0 56 112 0 0-56-112 0zM112 84l0 56 56 0 0-56-56 0zm168 0l0 112-56 0 0 56 56 0 0 56-56 0 0 56 56 0 0 112 56 0 0-392-56 0zm168 0l0 392 56 0 0-392-56 0zM168 140l0 56 56 0 0-56-56 0zm0 224l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm224 56l0 56 112 0 0-56-112 0z"],
    "sort": [560, 560, ["unsorted"], "f0dc", "M112 28l0 56-56 0 0 56 56 0 0 392 56 0 0-392 56 0 0-56-56 0 0-56-56 0zm280 0l0 392-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-392-56 0zM0 140l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm56 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "user": [504, 560, [128100, 62144, 62470, "user-alt", "user-large"], "f007", "M168 0l0 56 168 0 0-56-168 0zM112 56l0 168 56 0 0-168-56 0zm224 0l0 168 56 0 0-168-56 0zM168 224l0 56 168 0 0-56-168 0zM112 336l0 56 280 0 0-56-280 0zM56 392l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 448l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0z"],
    "signal-bars": [504, 560, ["signal-alt", "signal-alt-4", "signal-bars-strong"], "f690", "M392 28l0 56 56 0 0-56-56 0zM336 84l0 56-56 0 0 56-56 0 0 56-56 0 0 56-56 0 0 56-56 0 0 56-56 0 0 112 504 0 0-448-56 0 0 392-56 0 0-392-56 0zM280 196l56 0 0 280-56 0 0-280zM168 308l56 0 0 168-56 0 0-168zM56 420l56 0 0 56-56 0 0-56z"],
    "snowflake": [616, 560, [10052, 10054], "f2dc", "M280-28l0 56-56 0 0 56 56 0 0 168 56 0 0-168 56 0 0-56-56 0 0-56-56 0zM56 84l0 56-56 0 0 56 112 0 0-112-56 0zm448 0l0 112 112 0 0-56-56 0 0-56-56 0zM112 196l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM224 252l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 308l0 56 112 0 0-56-112 0zm168 0l0 168-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-168-56 0zm112 0l0 56 112 0 0-56-112 0zM0 364l0 56 56 0 0 56 56 0 0-112-112 0zm504 0l0 112 56 0 0-56 56 0 0-56-112 0z"],
    "ticket": [560, 560, [127903], "f145", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM224 168l0 56 112 0 0-56-112 0zM56 224l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM0 336l0 112 56 0 0-112-56 0zm224 0l0 56 112 0 0-56-112 0zm280 0l0 112 56 0 0-112-56 0zM56 448l0 56 448 0 0-56-448 0z"],
    "wheelchair": [504, 560, [], "f193", "M224-28l0 56 56 0 0-56-56 0zM168 28l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM224 84l0 56 56 0 0-56-56 0zM112 196l0 56 56 0 0-56-56 0zm112 0l0 168 56 0 0-112 112 0 0-56-168 0zM56 252l0 56 56 0 0-56-56 0zM0 308l0 168 56 0 0-168-56 0zm280 56l0 56 168 0 0-56-168 0zm168 56l0 168 56 0 0-168-56 0zM56 476l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 532l0 56 168 0 0-56-168 0z"],
    "bookmark": [448, 560, [128278, 61591], "f02e", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 504 112 0 0-56-56 0 0-448-56 0zm392 0l0 448-56 0 0 56 112 0 0-504-56 0zM168 392l0 56 112 0 0-56-112 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0z"],
    "bomb": [672, 560, [128163], "f1e2", "M560 0l0 56 56 0 0-56-56 0zM448 56l0 112 56 0 0-56 56 0 0-56-112 0zm168 0l0 56 56 0 0-56-56 0zM168 112l0 56 224 0 0-56-224 0zm392 0l0 56 56 0 0-56-56 0zM112 168l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 224l0 224 56 0 0-224-56 0zm392 0l0 224 56 0 0-224-56 0zM168 280l0 112 56 0 0-112-56 0zm56 112l0 56 112 0 0-56-112 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "arrow-right": [616, 560, [8594], "f061", "M336 28l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56-504 0 0 56 504 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zM448 364l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "battery-bolt": [672, 560, [], "f376", "M112 84l0 56 392 0 0-56-392 0zM56 140l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zM224 196l0 56 56 0 0 112 112 0 0-56-56 0 0-112-112 0zm392 0l0 168 56 0 0-168-56 0zM168 252l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 420l0 56 392 0 0-56-392 0z"],
    "mushroom": [616, 560, [127812], "e425", "M168-28l0 56 112 0 0 56 56 0 0-56 112 0 0-56-280 0zM112 28l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM56 84l0 56 56 0 0-56-56 0zm112 0l0 56 112 0 0-56-112 0zm336 0l0 56 56 0 0-56-56 0zM0 140l0 112 56 0 0-112-56 0zm448 0l0 56 56 0 0-56-56 0zm112 0l0 112 56 0 0-112-56 0zM392 196l0 56-336 0 0 56 56 0 0 196 56 0 0-196 280 0 0 196 56 0 0-196 56 0 0-56-112 0 0-56-56 0zM224 364l0 84 56 0 0-84-56 0zm112 0l0 84 56 0 0-84-56 0zM168 504l0 56 280 0 0-56-280 0z"],
    "buildings": [560, 560, [], "e0cc", "M280 28l0 56 224 0 0-56-224 0zM224 84l0 112-168 0 0 56-56 0 0 280 560 0 0-448-56 0 0 392-84 0 0-56-56 0 0 56-84 0 0-392-56 0zM56 252l168 0 0 224-168 0 0-224zm308-84l0 56 56 0 0-56-56 0zm0 112l0 56 56 0 0-56-56 0zM112 336l0 56 56 0 0-56-56 0z"],
    "xmark": [504, 560, [128473, 10005, 10006, 10060, 215, "close", "multiply", "remove", "times"], "f00d", "M0 28l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM56 84l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 140l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 196l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 364l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 420l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 476l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0z"],
    "lock-open": [560, 560, [], "f3c1", "M392 0l0 56 112 0 0-56-112 0zM336 56l0 112-224 0 0 56 336 0 0-56-56 0 0-112-56 0zm168 0l0 56 56 0 0-56-56 0zM56 224l0 280 56 0 0-280-56 0zm392 0l0 280 56 0 0-280-56 0zM224 336l0 56 112 0 0-56-112 0zM112 504l0 56 336 0 0-56-336 0z"],
    "droplet": [448, 560, [128167, "tint"], "f043", "M168 0l0 56 112 0 0-56-112 0zM112 56l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM56 168l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM0 280l0 168 56 0 0-168-56 0zm392 0l0 168 56 0 0-168-56 0zM112 336l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 504l0 56 224 0 0-56-224 0z"],
    "shop": [560, 560, ["store-alt"], "f54f", "M56 28l0 56 448 0 0-56-448 0zM0 84l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM168 140l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM56 196l0 280 56 0 0-224 56 0 0-56-112 0zm168 0l0 56 112 0 0-56-112 0zm168 0l0 56 56 0 0 224 56 0 0-280-112 0zM196 364l0 112-84 0 0 56 336 0 0-56-84 0 0-112-168 0zm56 112l0-56 56 0 0 56-56 0z"],
    "film": [560, 560, [127902, 62368, "film-alt", "film-simple"], "f008", "M56 28l0 56-56 0 0 364 56 0 0 56 448 0 0-56 56 0 0-364-56 0 0-56-448 0zm0 56l84 0 0 84-84 0 0-84zm0 364l0-84 84 0 0 84-84 0zm448 0l-84 0 0-84 84 0 0 84zm0-364l0 84-84 0 0-84 84 0zM196 448l0-364 168 0 0 364-168 0zM56 308l0-84 84 0 0 84-84 0zm364 0l0-84 84 0 0 84-84 0z"],
    "comments": [560, 560, [128490, 61670], "f086", "M224 28l0 56 280 0 0-56-280 0zM168 84l0 196 56 0 0-196-56 0zm336 0l0 196 56 0 0-196-56 0zM56 196l0 56 56 0 0-56-56 0zM0 252l0 196 56 0 0-196-56 0zm224 28l0 56 168 0 0-56-168 0zm224 0l0 56-56 0 0 56 112 0 0-112-56 0zM56 448l0 112 112 0 0-56-56 0 0-56-56 0zm112 0l0 56 224 0 0-56-224 0z"],
    "circle-check": [560, 560, [61533, "check-circle"], "f058", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM336 196l0 56 56 0 0-56-56 0zM168 252l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "moon": [560, 560, [127769, 9214], "f186", "M168 0l0 56 56 0 0 56 56 0 0-112-112 0zM112 56l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm112 0l0 168 56 0 0-168-56 0zM0 168l0 224 56 0 0-224-56 0zM224 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0 56 56 0 0-112-112 0zM280 336l0 56 168 0 0-56-168 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "plane": [616, 560, [], "f072", "M280 0l0 56 56 0 0-56-56 0zM224 56l0 112-56 0 0 56 112 0 0-168-56 0zm112 0l0 168 112 0 0-56-56 0 0-112-56 0zM56 224l0 56 112 0 0-56-112 0zm392 0l0 56 112 0 0-56-112 0zM0 280l0 112 224 0 0 56 56 0 0-112-224 0 0-56-56 0zm560 0l0 56-224 0 0 112 56 0 0-56 224 0 0-112-56 0zM168 448l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM224 504l0 56 168 0 0-56-168 0z"],
    "images": [560, 560, [], "f302", "M168 0l0 56 336 0 0-56-336 0zM112 56l0 336 56 0 0-336-56 0zm392 0l0 168-56 0 0 56 56 0 0 112 56 0 0-336-56 0zM224 112l0 56 56 0 0-56-56 0zM0 168l0 336 56 0 0-336-56 0zm392 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56-56 0 0 56 336 0 0-56-224 0 0-56-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "bag-shopping": [504, 560, ["shopping-bag"], "f290", "M196-28l0 56 112 0 0-56-112 0zM140 28l0 112-84 0 0 168 56 0 0-112 280 0 0 112 56 0 0-168-84 0 0-112-56 0 0 112-112 0 0-112-56 0zM0 308l0 168 56 0 0-168-56 0zm448 0l0 168 56 0 0-168-56 0zM56 476l0 56 392 0 0-56-392 0z"],
    "phone-volume": [616, 560, ["volume-control-phone"], "f2a0", "M56 0l0 56 112 0 0 56 56 0 0-112-168 0zM364 0l0 56 112 0 0-56-112 0zM0 56l0 196 56 0 0-196-56 0zm476 0l0 56 56 0 0-56-56 0zM224 112l0 84 56 0 0-84-56 0zm140 0l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zM420 168l0 56 56 0 0-56-56 0zM168 196l0 112 56 0 0-112-56 0zM56 252l0 112 56 0 0-112-56 0zm168 56l0 56 56 0 0-56-56 0zm168 0l0 56 84 0 0-56-84 0zM112 364l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zm196 0l0 56 56 0 0 112 56 0 0-168-112 0zM168 420l0 56 56 0 0-56-56 0zm56 56l0 56 112 0 0-56-112 0zm112 56l0 56 196 0 0-56-196 0z"],
    "phone": [616, 560, [128222, 128379], "f095", "M56 0l0 56 112 0 0 56 56 0 0-112-168 0zM0 56l0 196 56 0 0-196-56 0zm224 56l0 84 56 0 0-84-56 0zm-56 84l0 112 56 0 0-112-56 0zM56 252l0 112 56 0 0-112-56 0zm168 56l0 56 56 0 0-56-56 0zm168 0l0 56 84 0 0-56-84 0zM112 364l0 56 56 0 0-56-56 0zm168 0l0 56 112 0 0-56-112 0zm196 0l0 56 56 0 0 112 56 0 0-168-112 0zM168 420l0 56 56 0 0-56-56 0zm56 56l0 56 112 0 0-56-112 0zm112 56l0 56 196 0 0-56-196 0z"],
    "leaf": [504, 560, [], "f06c", "M224-28l0 56 56 0 0-56-56 0zM168 28l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 84l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 196l0 196 56 0 0-196-56 0zm224 0l0 252-112 0 0 56 112 0 0 112 56 0 0-112 112 0 0-56-112 0 0-252-56 0zm224 0l0 196 56 0 0-196-56 0zM56 392l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0z"],
    "heart-half": [616, 560, [], "e1ab", "M112 0l0 56 112 0 0-56-112 0zM56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM0 112l0 168 56 0 0-168-56 0zm280 0l0 336 56 0 0-336-56 0zM56 280l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "universal-access": [560, 560, [], "f29a", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm252 0l0 56-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zm252 0l0 224 56 0 0-224-56 0zM196 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "hourglass": [448, 560, [9203, 62032, "hourglass-empty"], "f254", "M0 0l0 56 56 0 0 140 56 0 0-140 224 0 0 140 56 0 0-140 56 0 0-56-448 0zM112 196l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 252l0 56 112 0 0-56-112 0zm-56 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM56 364l0 140-56 0 0 56 448 0 0-56-56 0 0-140-56 0 0 140-224 0 0-140-56 0z"],
    "fish": [616, 560, [128031], "f578", "M336-28l0 56 224 0 0-56-224 0zM112 28l0 112 56 0 0-56 168 0 0-56-224 0zm448 0l0 224 56 0 0-224-56 0zM392 112l0 56 56 0 0-56-56 0zM168 140l0 168 56 0 0-168-56 0zM448 252l0 56 56 0 0 112 56 0 0-168-112 0zM0 308l0 112 56 0 0-56 112 0 0-56-168 0zm336 0l0 56-56 0 0 56 112 0 0-112-56 0zM56 420l0 56 56 0 0-56-56 0zm168 0l0 112-56 0 0 56 112 0 0-168-56 0zm168 0l0 56 112 0 0-56-112 0zM112 476l0 56 56 0 0-56-56 0z"],
    "list": [504, 560, ["list-squares"], "f03a", "M0 84l0 56 56 0 0-56-56 0zm112 0l0 56 392 0 0-56-392 0zM0 252l0 56 56 0 0-56-56 0zm112 0l0 56 392 0 0-56-392 0zM0 420l0 56 56 0 0-56-56 0zm112 0l0 56 392 0 0-56-392 0z"],
    "headphones": [504, 560, [127911, 62863, "headphones-alt", "headphones-simple"], "f025", "M112 0l0 56 280 0 0-56-280 0zM56 56l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 112l0 364 56 0 0 56 112 0 0-252-112 0 0-168-56 0zM56 476l0-140 56 0 0 140-56 0zM448 112l0 168-112 0 0 252 112 0 0-56 56 0 0-364-56 0zm0 364l-56 0 0-140 56 0 0 140z"],
    "bus": [448, 560, [128653], "f207", "M56 0l0 56 336 0 0-56-336 0zM0 56l0 504 56 0 0-56 336 0 0 56 56 0 0-504-56 0 0 168-336 0 0-168-56 0zM56 448l0-168 336 0 0 168-336 0zm56-112l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0z"],
    "quote-left": [560, 560, [8220, "quote-left-alt"], "f10d", "M112 56l0 56 112 0 0-56-112 0zm336 0l0 56 112 0 0-56-112 0zM56 112l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 168l0 280 56 0 0-168 112 0 0 168 56 0 0-224-168 0 0-56-56 0zm336 0l0 280 56 0 0-168 112 0 0 168 56 0 0-224-168 0 0-56-56 0zM56 448l0 56 112 0 0-56-112 0zm336 0l0 56 112 0 0-56-112 0z"],
    "martini-glass": [504, 560, [127864, "glass-martini-alt"], "f57b", "M0 28l0 112 56 0 0-56 392 0 0 56 56 0 0-112-504 0zM56 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 196l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 252l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 168-112 0 0 56 280 0 0-56-112 0 0-168-56 0z"],
    "rectangle-wide": [672, 560, [], "f2fc", "M56 84l0 56 560 0 0-56-560 0zM0 140l0 280 56 0 0-280-56 0zm616 0l0 280 56 0 0-280-56 0zM56 420l0 56 560 0 0-56-560 0z"],
    "angle-down": [504, 560, [8964], "f107", "M0 168l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM56 224l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "ghost": [448, 560, [128123], "f6e2", "M112 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 448 56 0 0-56 112 0 0-56-112 0 0-336-56 0zm392 0l0 336-112 0 0 56 112 0 0 56 56 0 0-448-56 0zM140 168l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM168 504l0 56 112 0 0-56-112 0z"],
    "comment-dots": [560, 560, [128172, 62075, "commenting"], "f4ad", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 280 56 0 0-280-56 0zm504 0l0 280 56 0 0-280-56 0zM140 224l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 392l0 56 140 0 0 112 112 0 0-56-56 0 0-112-196 0zm308 0l0 56 140 0 0-56-140 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "chart-pie": [560, 560, ["pie-chart"], "f200", "M168 0l0 56 84 0 0 252 252 0 0 84 56 0 0-224-56 0 0 84-196 0 0-196 84 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "inbox": [504, 560, [], "f01c", "M56 28l0 56 392 0 0-56-392 0zM0 84l0 392 56 0 0-168 112 0 0-56-112 0 0-168-56 0zm448 0l0 168-112 0 0 56 112 0 0 168 56 0 0-392-56 0zM168 308l0 56 168 0 0-56-168 0zM56 476l0 56 392 0 0-56-392 0z"],
    "credit-card": [560, 560, [128179, 62083, "credit-card-alt"], "f09d", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 336 56 0 0-196 448 0 0 196 56 0 0-336-56 0 0 84-448 0 0-84-56 0zM56 448l0 56 448 0 0-56-448 0z"],
    "wand-magic-sparkles": [560, 560, ["magic-wand-sparkles"], "e2ca", "M56 0l0 56-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zM392 0l0 56 112 0 0-56-112 0zM336 56l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zM280 112l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM168 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM112 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 336l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 392l0 112 56 0 0-112-56 0zm224 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 504l0 56 112 0 0-56-112 0z"],
    "bell-slash": [560, 560, [128277, 61943], "f1f6", "M0 0L0 56 56 56 56 0 0 0zM196 0l0 56 168 0 0-56-168 0zM56 56l0 56 56 0 0-56-56 0zm308 0l0 56 56 0 0-56-56 0zM112 112l0 56 56 0 0-56-56 0zm308 0l0 168 56 0 0-168-56 0zM168 168l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM28 280l0 112 56 0 0-112-56 0zm252 0l0 56 56 0 0-56-56 0zm196 0l0 56 56 0 0-56-56 0zM336 336l0 56 56 0 0-56-56 0zM84 392l0 56 84 0 0 56 56 0 0-56 56 0 0-56-196 0zm308 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM224 504l0 56 112 0 0-56-112 0zm280 0l0 56 56 0 0-56-56 0z"],
    "folders": [560, 560, [], "f660", "M168 28l0 56 140 0 0-56-140 0zM112 84l0 252 56 0 0-252-56 0zm196 0l0 56 56 0 0-56-56 0zm56 56l0 56 140 0 0 140 56 0 0-196-196 0zM0 196l0 252 56 0 0-252-56 0zM168 336l0 56 336 0 0-56-336 0zM56 448l0 56 336 0 0-56-336 0z"],
    "star": [672, 560, [11088, 61446], "f005", "M308-56l0 112 56 0 0-112-56 0zM252 56l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM0 168l0 56 56 0 0 56 56 0 0-56 140 0 0-56-252 0zm420 0l0 56 140 0 0 56 56 0 0-56 56 0 0-56-252 0zM112 280l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM168 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 168 112 0 0-56-56 0 0-112-56 0zm168 0l0 56 112 0 0-56-112 0zm224 0l0 112-56 0 0 56 112 0 0-168-56 0zM224 448l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0z"],
    "flag": [504, 560, [127988, 61725], "f024", "M0 28l0 504 56 0 0-392 112 0 0-56-112 0 0-56-56 0zm168 0l0 56 168 0 0-56-168 0zM336 84l0 56 112 0 0 224-112 0 0 56 168 0 0-336-168 0zM168 308l0 56 168 0 0-56-168 0z"],
    "triangle-exclamation": [560, 560, [9888, "exclamation-triangle", "warning"], "f071", "M224 0l0 56 112 0 0-56-112 0zM168 56l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM112 168l0 112 56 0 0-112-56 0zm140 0l0 168 56 0 0-168-56 0zm140 0l0 112 56 0 0-112-56 0zM56 280l0 112 56 0 0-112-56 0zm392 0l0 112 56 0 0-112-56 0zM0 392l0 112 56 0 0-112-56 0zm252 0l0 56 56 0 0-56-56 0zm252 0l0 112 56 0 0-112-56 0zM56 504l0 56 448 0 0-56-448 0z"],
    "lock": [448, 560, [128274], "f023", "M168 0l0 56 112 0 0-56-112 0zM112 56l0 112-56 0 0 56 336 0 0-56-56 0 0-112-56 0 0 112-112 0 0-112-56 0zM0 224l0 280 56 0 0-280-56 0zm392 0l0 280 56 0 0-280-56 0zM196 308l0 112 56 0 0-112-56 0zM56 504l0 56 336 0 0-56-336 0z"],
    "anchor": [504, 560, [9875], "f13d", "M224 0l0 56 56 0 0-56-56 0zM168 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56-112 0 0 56 112 0 0 280-56 0 0 56 168 0 0-56-56 0 0-280 112 0 0-56-112 0 0-56-56 0zM0 280l0 112 56 0 0-56 56 0 0-56-112 0zm392 0l0 56 56 0 0 56 56 0 0-112-112 0zM56 392l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "scissors": [504, 560, [9984, 9986, 9988, "cut"], "f0c4", "M56 0l0 56 112 0 0-56-112 0zM0 56l0 112 56 0 0-112-56 0zm168 0l0 112-112 0 0 56 168 0 0-168-56 0zm280 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM224 224l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 336l0 56 112 0 0 112 56 0 0-168-168 0zm280 0l0 56 56 0 0-56-56 0zM0 392l0 112 56 0 0-112-56 0zm392 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 504l0 56 112 0 0-56-112 0z"],
    "bug": [560, 560, [], "f188", "M168 0l0 56 56 0 0-56-56 0zM336 0l0 56 56 0 0-56-56 0zM224 56l0 56 112 0 0-56-112 0zM0 112l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM56 168l0 56 56 0 0 56-112 0 0 56 112 0 0 56-56 0 0 56 112 0 0-168 224 0 0 168 112 0 0-56-56 0 0-56 112 0 0-56-112 0 0-56 56 0 0-56-112 0 0 56-224 0 0-56-112 0zM0 448l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 504l0 56 112 0 0-56-112 0z"],
    "arrow-right-from-bracket": [616, 560, ["sign-out"], "f08b", "M84 28l0 56 112 0 0-56-112 0zM28 84l0 392 56 0 0-392-56 0zm364 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56-308 0 0 56 308 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zM448 364l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM84 476l0 56 112 0 0-56-112 0z"],
    "arrow-right-arrow-left": [504, 560, [8644, "exchange"], "f0ec", "M336 0l0 56 56 0 0-56-56 0zm56 56l0 56-392 0 0 56 392 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zM336 224l0 56 56 0 0-56-56 0zM112 280l0 56 56 0 0-56-56 0zM56 336l0 56-56 0 0 56 56 0 0 56 56 0 0-56 392 0 0-56-392 0 0-56-56 0zm56 168l0 56 56 0 0-56-56 0z"],
    "arrow-up-from-line": [448, 560, [8613, "arrow-from-bottom"], "f342", "M196-28l0 56-56 0 0 56 56 0 0 308 56 0 0-308 56 0 0-56-56 0 0-56-56 0zM84 84l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM28 140l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 504l0 56 448 0 0-56-448 0z"],
    "battery-full": [672, 560, [128267, "battery", "battery-5"], "f240", "M112 84l0 56 392 0 0-56-392 0zM56 140l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zM168 196l0 168 56 0 0-168-56 0zm112 0l0 168 56 0 0-168-56 0zm112 0l0 168 56 0 0-168-56 0zm224 0l0 168 56 0 0-168-56 0zM112 420l0 56 392 0 0-56-392 0z"],
    "font": [616, 560, [], "f031", "M280 0l0 56 56 0 0-56-56 0zM224 56l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM168 168l0 112 56 0 0-112-56 0zm224 0l0 112 56 0 0-112-56 0zM112 280l0 112 392 0 0-112-56 0 0 56-280 0 0-56-56 0zM56 392l0 84-56 0 0 56 168 0 0-56-56 0 0-84-56 0zm448 0l0 84-56 0 0 56 168 0 0-56-56 0 0-84-56 0z"],
    "language": [616, 560, [], "f1ab", "M168 0l0 56-168 0 0 56 280 0 0 112 56 0 0-112 56 0 0-56-168 0 0-56-56 0zM56 168l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 280l0 56 56 0 0-56-56 0zm168 0l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM112 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM0 392l0 56 112 0 0-56-112 0zm280 0l0 112 280 0 0-112-56 0 0 56-168 0 0-56-56 0zM224 504l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0z"],
    "caret-up": [504, 560, [], "f0d8", "M224 112l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 280l0 56-56 0 0 56 504 0 0-56-56 0 0-56-56 0 0 56-280 0 0-56-56 0z"],
    "person-biking": [616, 560, [128692, "biking"], "f84a", "M392 28l0 56 56 0 0-56-56 0zM224 84l0 56 112 0 0 56 56 0 0-112-168 0zm-56 56l0 112 56 0 0-112-56 0zm112 56l0 56 56 0 0-56-56 0zm112 0l0 56 112 0 0-56-112 0zM224 252l0 56 56 0 0-56-56 0zM56 308l0 56 112 0 0-56-112 0zm224 0l0 168 56 0 0-168-56 0zm168 0l0 56 112 0 0-56-112 0zM0 364l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zm224 0l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM56 476l0 56 112 0 0-56-112 0zm392 0l0 56 112 0 0-56-112 0z"],
    "globe": [560, 560, [127760], "f0ac", "M168 0l0 56 56 0 0 112 56 0 0-112 112 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-84 112 0 0 84 56 0 0-84 112 0 0 84 56 0 0-84 112 0 0 84 56 0 0-224-56 0 0 84-112 0 0-84-56 0 0 84-112 0 0-84-56 0 0 84-112 0 0-84-56 0zM56 392l0 56 56 0 0-56-56 0zm224 0l0 112-112 0 0 56 224 0 0-56-56 0 0-112-56 0zm168 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0z"],
    "computer-classic": [504, 560, [], "f8b1", "M56 0l0 56 392 0 0-56-392 0zM0 56l0 420 56 0 0-420-56 0zm448 0l0 420 56 0 0-420-56 0zM112 112l0 196 280 0 0-196-280 0zm56 140l0-84 168 0 0 84-168 0zM112 364l0 56 56 0 0-56-56 0zm112 0l0 56 168 0 0-56-168 0zM56 476l0 112 392 0 0-112-56 0 0 56-280 0 0-56-56 0z"],
    "arrow-left": [616, 560, [8592], "f060", "M224 28l0 56 56 0 0-56-56 0zM168 84l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM56 196l0 56-56 0 0 56 56 0 0 56 56 0 0-56 504 0 0-56-504 0 0-56-56 0zm56 168l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "paw": [616, 560, [], "f1b0", "M224 0l0 56 56 0 0-56-56 0zM336 0l0 56 56 0 0-56-56 0zM168 56l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM56 112l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM0 168l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm112 0l0 112 56 0 0-112-56 0zm112 0l0 112 56 0 0-112-56 0zM56 280l0 56 56 0 0-56-56 0zm112 0l0 56 280 0 0-56-280 0zm336 0l0 56 56 0 0-56-56 0zM112 336l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM56 392l0 112 56 0 0-112-56 0zm448 0l0 112 56 0 0-112-56 0zM280 448l0 56 56 0 0-56-56 0zM112 504l0 56 168 0 0-56-168 0zm224 0l0 56 168 0 0-56-168 0z"],
    "bed": [560, 560, [128716], "f236", "M0 56l0 448 56 0 0-56 448 0 0 56 56 0 0-224-56 0 0-56-280 0 0-56-56 0 0-56-112 0 0-56-56 0zM504 280l0 112-448 0 0-112 448 0zM168 168l0 56-112 0 0-56 112 0z"],
    "landmark": [560, 560, [127963], "f66f", "M224 0l0 56 112 0 0-56-112 0zM112 56l0 56 112 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zM0 112l0 168 84 0 0 196-84 0 0 56 560 0 0-56-84 0 0-196 84 0 0-168-112 0 0 56 56 0 0 56-448 0 0-56 56 0 0-56-112 0zM140 476l0-196 112 0 0 196-112 0zm168 0l0-196 112 0 0 196-112 0z"],
    "music": [560, 560, [127925], "f001", "M364 28l0 56 140 0 0 56-140 0 0 56 140 0 0 112-112 0 0 56 112 0 0 56 56 0 0-392-196 0zM168 84l0 280-112 0 0 56 112 0 0 56 56 0 0-224 140 0 0-56-140 0 0-56 140 0 0-56-196 0zM336 364l0 56 56 0 0-56-56 0zM0 420l0 56 56 0 0-56-56 0zm392 0l0 56 112 0 0-56-112 0zM56 476l0 56 112 0 0-56-112 0z"],
    "files": [504, 560, [], "e178", "M168 0l0 56 112 0 0 168 168 0 0 168 56 0 0-280-56 0 0 56-112 0 0-112 56 0 0-56-224 0zM112 56l0 336 56 0 0-336-56 0zm280 0l0 56 56 0 0-56-56 0zM0 168l0 336 56 0 0-336-56 0zM168 392l0 56 280 0 0-56-280 0zM56 504l0 56 280 0 0-56-280 0z"],
    "plus": [504, 560, [10133, 61543, "add"], "2b", "M224 28l0 224-224 0 0 56 224 0 0 224 56 0 0-224 224 0 0-56-224 0 0-224-56 0z"],
    "box": [560, 560, [128230], "f466", "M112 0l0 112-56 0 0 112-56 0 0 252 56 0 0-196 196 0 0 56 56 0 0-56 196 0 0 196 56 0 0-252-56 0 0-112-56 0 0-112-336 0zm0 112l56 0 0-56 84 0 0 168-140 0 0-112zm336 0l0 112-140 0 0-168 84 0 0 56 56 0zM56 476l0 56 448 0 0-56-448 0z"],
    "arrow-rotate-right": [560, 560, [8635, "arrow-right-rotate", "arrow-rotate-forward", "redo"], "f01e", "M168 0l0 56 224 0 0-56-224 0zM504 0l0 112-56 0 0 56-112 0 0 56 224 0 0-224-56 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "door-closed": [504, 560, [128682], "f52a", "M112 0l0 56 280 0 0-56-280 0zM56 56l0 448-56 0 0 56 504 0 0-56-56 0 0-448-56 0 0 448-280 0 0-448-56 0zM280 252l0 56 56 0 0-56-56 0z"],
    "arrow-down-wide-short": [560, 560, ["sort-amount-asc", "sort-amount-down"], "f160", "M112 28l0 392-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-392-56 0zM336 84l0 56 224 0 0-56-224 0zm0 112l0 56 168 0 0-56-168 0zm0 112l0 56 112 0 0-56-112 0zM0 364l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm112 56l0 56 56 0 0-56-56 0z"],
    "caret-down": [504, 560, [], "f0d7", "M0 168l0 56 56 0 0 56 56 0 0-56 280 0 0 56 56 0 0-56 56 0 0-56-504 0zM112 280l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM168 336l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "compress": [560, 560, [], "f066", "M168 0l0 168-168 0 0 56 224 0 0-224-56 0zM336 0l0 224 224 0 0-56-168 0 0-168-56 0zM0 336l0 56 168 0 0 168 56 0 0-224-224 0zm336 0l0 224 56 0 0-168 168 0 0-56-224 0z"],
    "eye-slash": [560, 560, [], "f070", "M0 0L0 56 56 56 56 0 0 0zM56 56l0 56 56 0 0-56-56 0zm168 0l0 56 168 0 0-56-168 0zM112 112l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 168l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 224l0 112 56 0 0-112-56 0zm224 0l0 56 56 0 0-56-56 0zm280 0l0 112 56 0 0-112-56 0zM280 280l0 56 56 0 0-56-56 0zM56 336l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 448l0 56 168 0 0-56-168 0zm280 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "mug-hot": [560, 560, [9749], "f7b6", "M112 0l0 112 56 0 0-112-56 0zM224 0l0 112 56 0 0-112-56 0zM336 0l0 112 56 0 0-112-56 0zM56 168l0 336 56 0 0-280 280 0 0 280 56 0 0-112 56 0 0-56 56 0 0-168-504 0zM504 336l-56 0 0-112 56 0 0 112zM112 504l0 56 280 0 0-56-280 0z"],
    "bolt": [448, 560, [9889, "zap"], "f0e7", "M168-56l0 56 56 0 0 224 168 0 0 112 56 0 0-168-168 0 0-224-112 0zM112 0l0 112 56 0 0-112-56 0zM56 112l0 112 56 0 0-112-56 0zM0 224l0 168 168 0 0 224 112 0 0-56-56 0 0-224-168 0 0-112-56 0zM336 336l0 112 56 0 0-112-56 0zM280 448l0 112 56 0 0-112-56 0z"],
    "arrow-rotate-left": [560, 560, [8634, "arrow-left-rotate", "arrow-rotate-back", "arrow-rotate-backward", "undo"], "f0e2", "M0 0l0 224 224 0 0-56-112 0 0-56-56 0 0-112-56 0zM168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 224 56 0 0-224-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "burst": [560, 560, [], "e4dc", "M308 560l-56 0 0-140 56 0 0 140zM84 532l-56 0 0-56 56 0 0 56zm448 0l-56 0 0-56 56 0 0 56zM140 476l-56 0 0-56 56 0 0 56zm336 0l-56 0 0-56 56 0 0 56zM196 420l-56 0 0-56 56 0 0 56zm224 0l-56 0 0-56 56 0 0 56zM140 308l-140 0 0-56 140 0 0 56zm168 0l-56 0 0-56 56 0 0 56zm252 0l-140 0 0-56 140 0 0 56zM196 196l-56 0 0-56 56 0 0 56zm224 0l-56 0 0-56 56 0 0 56zM140 140l-56 0 0-56 56 0 0 56zm168 0l-56 0 0-140 56 0 0 140zm168 0l-56 0 0-56 56 0 0 56zM84 84l-56 0 0-56 56 0 0 56zm448 0l-56 0 0-56 56 0 0 56z"],
    "wifi": [392, 560, ["wifi-3", "wifi-strong"], "f1eb", "M112 196l0 56 168 0 0-56-168 0zM56 252l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM0 308l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM168 364l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "volume": [560, 560, [128265, "volume-medium"], "f6a8", "M224 28l0 56 56 0 0 392-56 0 0 56 112 0 0-504-112 0zM168 84l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 140l0 56 112 0 0-56-112 0zm448 0l0 252 56 0 0-252-56 0zM0 196l0 168 56 0 0-168-56 0zm392 0l0 140 56 0 0-140-56 0zM56 364l0 56 112 0 0-56-112 0zm392 28l0 56 56 0 0-56-56 0zM168 420l0 56 56 0 0-56-56 0z"],
    "tag": [560, 560, [127991], "f02b", "M56 0l0 56 224 0 0-56-224 0zM0 56l0 224 56 0 0-224-56 0zm280 0l0 56 56 0 0-56-56 0zM112 112l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0zM56 280l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM112 336l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM168 392l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM224 448l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0z"],
    "compact-disc": [560, 560, [128191, 128192, 128440], "f51f", "M168 0l0 56 112 0 0 112-56 0 0 56 112 0 0-168 56 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM168 224l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM224 336l0 168-56 0 0 56 224 0 0-56-112 0 0-112 56 0 0-56-112 0zM56 392l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0z"],
    "phone-slash": [616, 560, [], "f3dd", "M56 0l0 56 112 0 0 56 56 0 0-112-168 0zM532 0l0 56 56 0 0-56-56 0zM0 56l0 196 56 0 0-196-56 0zm476 0l0 56 56 0 0-56-56 0zM224 112l0 84 56 0 0-84-56 0zm196 0l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zM168 196l0 56 56 0 0-56-56 0zm140 28l0 56 56 0 0-56-56 0zM56 252l0 112 56 0 0-112-56 0zm196 28l0 56 56 0 0-56-56 0zm140 28l0 56 84 0 0-56-84 0zM196 336l0 56 56 0 0-56-56 0zm140 28l0 56 56 0 0-56-56 0zm140 0l0 56 56 0 0 112 56 0 0-168-112 0zM140 392l0 56 56 0 0-56-56 0zM84 448l0 56 56 0 0-56-56 0zm140 28l0 56 112 0 0-56-112 0zM28 504l0 56 56 0 0-56-56 0zm308 28l0 56 196 0 0-56-196 0z"],
    "arrow-up-right-from-square": [504, 560, ["external-link"], "f08e", "M56 28l0 56 168 0 0-56-168 0zm280 0l0 56 56 0 0 56 56 0 0 56 56 0 0-168-168 0zM0 84l0 392 56 0 0-392-56 0zm336 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm280 0l0 168 56 0 0-168-56 0zM56 476l0 56 392 0 0-56-392 0z"],
    "rectangle-tall": [392, 560, [], "e791", "M56-56l0 56 280 0 0-56-280 0zM0 0L0 560 56 560 56 0 0 0zM336 0l0 560 56 0 0-560-56 0zM56 560l0 56 280 0 0-56-280 0z"],
    "compass": [560, 560, [129517], "f14e", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm280 0l0 56 56 0 0 56 56 0 0-112-112 0zm224 0l0 224 56 0 0-224-56 0zM224 224l0 56 56 0 0-56-56 0zm-56 56l0 112 112 0 0-56-56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "square": [504, 560, [9632, 9723, 9724, 61590], "f0c8", "M56 28l0 56 392 0 0-56-392 0zM0 84l0 392 56 0 0-392-56 0zm448 0l0 392 56 0 0-392-56 0zM56 476l0 56 392 0 0-56-392 0z"],
    "face-grin": [560, 560, [128512, "grin"], "f580", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM168 196l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 308l0 56 56 0 0 56 112 0 0-56 56 0 0-56-224 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "angle-up": [504, 560, [8963], "f106", "M224 112l0 56 56 0 0-56-56 0zm-56 56l0 56 56 0 0-56-56 0zm112 0l0 56 56 0 0-56-56 0zM112 224l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0zM56 280l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM0 336l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0z"],
    "face-smile": [560, 560, [128578, "smile"], "f118", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM168 196l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 308l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM224 364l0 56 112 0 0-56-112 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "umbrella": [560, 560, [], "f0e9", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM0 168l0 112 56 0 0-112-56 0zm504 0l0 112 56 0 0-112-56 0zM56 280l0 56 196 0 0 168 56 0 0-168 196 0 0-56-448 0zm84 168l0 56 56 0 0-56-56 0zm56 56l0 56 56 0 0-56-56 0z"],
    "pause": [504, 560, [9208], "f04c", "M56 28l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0zM0 84l0 392 56 0 0-392-56 0zm168 0l0 392 56 0 0-392-56 0zm112 0l0 392 56 0 0-392-56 0zm168 0l0 392 56 0 0-392-56 0zM56 476l0 56 112 0 0-56-112 0zm280 0l0 56 112 0 0-56-112 0z"],
    "desktop": [560, 560, [128421, 61704, "desktop-alt"], "f390", "M56 56l0 56 448 0 0-56-448 0zM0 112l0 252 56 0 0-252-56 0zm504 0l0 252 56 0 0-252-56 0zM56 364l0 56 196 0 0 56-84 0 0 56 224 0 0-56-84 0 0-56 196 0 0-56-448 0z"],
    "arrow-down": [504, 560, [8595], "f063", "M224-28l0 504-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-504-56 0zM0 308l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM56 364l0 56 56 0 0-56-56 0zm336 0l0 56 56 0 0-56-56 0zM112 420l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "location-dot": [448, 560, ["map-marker-alt"], "f3c5", "M112 0l0 56 224 0 0-56-224 0zM56 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 112l0 224 56 0 0-224-56 0zm168 0l0 56 112 0 0-56-112 0zm224 0l0 224 56 0 0-224-56 0zM112 168l0 112 56 0 0-112-56 0zm168 0l0 112 56 0 0-112-56 0zM168 280l0 56 112 0 0-56-112 0zM56 336l0 112 56 0 0-112-56 0zm280 0l0 112 56 0 0-112-56 0zM112 448l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM168 504l0 56 112 0 0-56-112 0z"],
    "bars": [504, 560, ["navicon"], "f0c9", "M0 84l0 56 504 0 0-56-504 0zM0 252l0 56 504 0 0-56-504 0zM0 420l0 56 504 0 0-56-504 0z"],
    "battery-exclamation": [672, 560, [], "e0b0", "M224 476l-112 0 0-56 112 0 0 56zm112 0l-56 0 0-56 56 0 0 56zm168 0l-112 0 0-56 112 0 0 56zM112 420l-56 0 0-280 56 0 0 280zm448 0l-56 0 0-280 56 0 0 280zM336 364l-56 0 0-280 56 0 0 280zm336 0l-56 0 0-168 56 0 0 168zM224 140l-112 0 0-56 112 0 0 56zm280 0l-112 0 0-56 112 0 0 56z"],
    "battery-empty": [672, 560, ["battery-0"], "f244", "M112 84l0 56 392 0 0-56-392 0zM56 140l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zm112 56l0 168 56 0 0-168-56 0zM112 420l0 56 392 0 0-56-392 0z"],
    "keyboard": [616, 560, [9000], "f11c", "M560 532l-504 0 0-56 504 0 0 56zM56 476l-56 0 0-392 56 0 0 392zm560 0l-56 0 0-392 56 0 0 392zM448 420l-280 0 0-56 280 0 0 56zM168 308l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zM168 196l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zm112 0l-56 0 0-56 56 0 0 56zM560 84l-504 0 0-56 504 0 0 56z"],
    "stopwatch": [448, 560, [9201], "f2f2", "M112 0l0 56 84 0 0 56-84 0 0 56 224 0 0-56-84 0 0-56 84 0 0-56-224 0zM392 112l0 56 56 0 0-56-56 0zM56 168l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM0 224l0 224 56 0 0-224-56 0zm196 0l0 140 56 0 0-140-56 0zm196 0l0 224 56 0 0-224-56 0zM56 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM112 504l0 56 224 0 0-56-224 0z"],
    "train": [448, 560, [128646], "f238", "M56 0l0 56-56 0 0 364 56 0 0-168 336 0 0 168 56 0 0-364-56 0 0-56-336 0zm0 56l140 0 0 140-140 0 0-140zm336 0l0 140-140 0 0-140 140 0zM140 308l0 56 168 0 0-56-168 0zM56 420l0 112 56 0 0-56 224 0 0 56 56 0 0-112-336 0zM0 532l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0z"],
    "gamepad": [616, 560, [], "f11b", "M112 56l0 56 392 0 0-56-392 0zM56 112l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm560 0l0 224 56 0 0-224-56 0zM168 196l0 56-56 0 0 56 56 0 0 56 56 0 0-56 56 0 0-56-56 0 0-56-56 0zm168 28l0 56 56 0 0-56-56 0zm112 56l0 56 56 0 0-56-56 0zM56 392l0 56 56 0 0-56-56 0zm448 0l0 56 56 0 0-56-56 0zM112 448l0 56 392 0 0-56-392 0z"],
    "ship": [616, 560, [128674], "f21a", "M168 28l0 56 280 0 0-56-280 0zM112 84l0 168 112 0 0-56-56 0 0-112-56 0zm336 0l0 112-56 0 0 56 112 0 0-168-56 0zM224 140l0 56 56 0 0 168 56 0 0-168 56 0 0-56-168 0zM56 252l0 168 56 0 0-168-56 0zm448 0l0 168 56 0 0-168-56 0zM112 420l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM0 476l0 56 112 0 0-56-112 0zm168 0l0 56 112 0 0-56-112 0zm168 0l0 56 112 0 0-56-112 0zm168 0l0 56 112 0 0-56-112 0z"],
    "arrows-rotate": [560, 560, [128472, "refresh", "sync"], "f021", "M168 0l0 56 224 0 0-56-224 0zM504 0l0 112-56 0 0 56-112 0 0 56 224 0 0-224-56 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zM0 336l0 224 56 0 0-112 56 0 0-56 112 0 0-56-224 0zm448 56l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "book-open": [560, 560, [128214, 128366], "f518", "M0 56l0 392 56 0 0-336 196 0 0-56-252 0zm308 0l0 56 196 0 0 336 56 0 0-392-252 0zm-56 56l0 336-196 0 0 56 196 0 0 56 56 0 0-56 196 0 0-56-196 0 0-336-56 0z"],
    "box-archive": [560, 560, ["archive"], "f187", "M448 532l-336 0 0-56 336 0 0 56zM560 196l-56 0 0 280-56 0 0-280-336 0 0 280-56 0 0-280-56 0 0-168 560 0 0 168zM364 308l-168 0 0-56 168 0 0 56zM56 140l448 0 0-56-448 0 0 56z"],
    "cake-candles": [504, 560, [127874, "birthday-cake", "cake"], "f1fd", "M112 0l0 56 56 0 0-56-56 0zM224 0l0 56 56 0 0-56-56 0zM336 0l0 56 56 0 0-56-56 0zM112 112l0 56-56 0 0 56 392 0 0-56-56 0 0-56-56 0 0 56-56 0 0-56-56 0 0 56-56 0 0-56-56 0zM0 224l0 308 504 0 0-308-56 0 0 112-84 0 0 56 84 0 0 84-392 0 0-84 84 0 0-56-84 0 0-112-56 0zm140 56l0 56 56 0 0-56-56 0zm168 0l0 56 56 0 0-56-56 0zM196 336l0 56 112 0 0-56-112 0z"],
    "signal-bars-good": [504, 560, ["signal-alt-3"], "f693", "M280 140l0 56 56 0 0-56-56 0zm-56 56l0 56-56 0 0 56-56 0 0 56-56 0 0 56-56 0 0 112 392 0 0-336-56 0 0 280-56 0 0-280-56 0zM168 308l56 0 0 168-56 0 0-168zM56 420l56 0 0 56-56 0 0-56z"],
    "circle-info": [560, 560, ["info-circle"], "f05a", "M168 0l0 56 224 0 0-56-224 0zM112 56l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM56 112l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM252 140l0 56 56 0 0-56-56 0zM0 168l0 224 56 0 0-224-56 0zm504 0l0 224 56 0 0-224-56 0zM196 252l0 56 56 0 0 56-56 0 0 56 168 0 0-56-56 0 0-112-112 0zM56 392l0 56 56 0 0-56-56 0zm392 0l0 56 56 0 0-56-56 0zM112 448l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0zM168 504l0 56 224 0 0-56-224 0z"],
    "layer-group": [560, 560, [], "f5fd", "M224-28l0 56 112 0 0-56-112 0zM112 28l0 56 112 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zM0 84l0 56 112 0 0-56-112 0zm448 0l0 56 112 0 0-56-112 0zM112 140l0 56 112 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zM224 196l0 56 112 0 0-56-112 0zM0 252l0 56 112 0 0-56-112 0zm448 0l0 56 112 0 0-56-112 0zM112 308l0 56 112 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zM224 364l0 56 112 0 0-56-112 0zM0 420l0 56 112 0 0-56-112 0zm448 0l0 56 112 0 0-56-112 0zM112 476l0 56 112 0 0-56-112 0zm224 0l0 56 112 0 0-56-112 0zM224 532l0 56 112 0 0-56-112 0z"],
    "shield": [504, 560, [128737, "shield-blank"], "f132", "M56 0l0 56 168 0 0 448-56 0 0 56 168 0 0-56-56 0 0-448 168 0 0-56-392 0zM0 56l0 280 56 0 0-280-56 0zm448 0l0 280 56 0 0-280-56 0zM56 336l0 112 56 0 0-112-56 0zm336 0l0 112 56 0 0-112-56 0zM112 448l0 56 56 0 0-56-56 0zm224 0l0 56 56 0 0-56-56 0z"],
    "cart-shopping": [560, 560, [128722, "shopping-cart"], "f07a", "M0 28l0 56 56 0 0 392 56 0 0-56 56 0 0 56 56 0 0-56 112 0 0 56 56 0 0-56 56 0 0-56-336 0 0-224 392 0 0-56-392 0 0-56-112 0zM504 140l0 112 56 0 0-112-56 0zM448 252l0 112 56 0 0-112-56 0zm0 168l0 56 56 0 0-56-56 0zM112 476l0 56 56 0 0-56-56 0zm280 0l0 56 56 0 0-56-56 0z"]
  };

  bunker(function () {
    defineIcons('fapr', icons);
  });

}());

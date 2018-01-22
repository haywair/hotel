(function (e) {
    "use strict";
    var n = {};
    var t = /d{1,4}|M{1,4}|YY(?:YY)?|S{1,3}|Do|ZZ|([HhMsDm])\1?|[aA]|"[^"]*"|'[^']*'/g;
    var r = /\d\d?/;
    var u = /\d{3}/;
    var o = /\d{4}/;
    var a = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i;
    var i = /\[([^]*?)\]/gm;
    var s = function () {
    };

    function f(e, n) {
        var t = [];
        for (var r = 0, u = e.length; r < u; r++) {
            t.push(e[r].substr(0, n))
        }
        return t
    }

    function m(e) {
        return function (n, t, r) {
            var u = r[e].indexOf(t.charAt(0).toUpperCase() + t.substr(1).toLowerCase());
            if (~u) {
                n.month = u
            }
        }
    }

    function d(e, n) {
        e = String(e);
        n = n || 2;
        while (e.length < n) {
            e = "0" + e
        }
        return e
    }

    var c = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var l = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var h = f(l, 3);
    var M = f(c, 3);
    n.i18n = {
        dayNamesShort: M,
        dayNames: c,
        monthNamesShort: h,
        monthNames: l,
        amPm: ["am", "pm"],
        DoFn: function e(n) {
            return n + ["th", "st", "nd", "rd"][n % 10 > 3 ? 0 : (n - n % 10 !== 10) * n % 10]
        }
    };
    var g = {
        D: function (e) {
            return e.getDate()
        }, DD: function (e) {
            return d(e.getDate())
        }, Do: function (e, n) {
            return n.DoFn(e.getDate())
        }, d: function (e) {
            return e.getDay()
        }, dd: function (e) {
            return d(e.getDay())
        }, ddd: function (e, n) {
            return n.dayNamesShort[e.getDay()]
        }, dddd: function (e, n) {
            return n.dayNames[e.getDay()]
        }, M: function (e) {
            return e.getMonth() + 1
        }, MM: function (e) {
            return d(e.getMonth() + 1)
        }, MMM: function (e, n) {
            return n.monthNamesShort[e.getMonth()]
        }, MMMM: function (e, n) {
            return n.monthNames[e.getMonth()]
        }, YY: function (e) {
            return String(e.getFullYear()).substr(2)
        }, YYYY: function (e) {
            return e.getFullYear()
        }, h: function (e) {
            return e.getHours() % 12 || 12
        }, hh: function (e) {
            return d(e.getHours() % 12 || 12)
        }, H: function (e) {
            return e.getHours()
        }, HH: function (e) {
            return d(e.getHours())
        }, m: function (e) {
            return e.getMinutes()
        }, mm: function (e) {
            return d(e.getMinutes())
        }, s: function (e) {
            return e.getSeconds()
        }, ss: function (e) {
            return d(e.getSeconds())
        }, S: function (e) {
            return Math.round(e.getMilliseconds() / 100)
        }, SS: function (e) {
            return d(Math.round(e.getMilliseconds() / 10), 2)
        }, SSS: function (e) {
            return d(e.getMilliseconds(), 3)
        }, a: function (e, n) {
            return e.getHours() < 12 ? n.amPm[0] : n.amPm[1]
        }, A: function (e, n) {
            return e.getHours() < 12 ? n.amPm[0].toUpperCase() : n.amPm[1].toUpperCase()
        }, ZZ: function (e) {
            var n = e.getTimezoneOffset();
            return (n > 0 ? "-" : "+") + d(Math.floor(Math.abs(n) / 60) * 100 + Math.abs(n) % 60, 4)
        }
    };
    var D = {
        D: [r, function (e, n) {
            e.day = n
        }],
        Do: [new RegExp(r.source + a.source), function (e, n) {
            e.day = parseInt(n, 10)
        }],
        M: [r, function (e, n) {
            e.month = n - 1
        }],
        YY: [r, function (e, n) {
            var t = new Date, r = +("" + t.getFullYear()).substr(0, 2);
            e.year = "" + (n > 68 ? r - 1 : r) + n
        }],
        h: [r, function (e, n) {
            e.hour = n
        }],
        m: [r, function (e, n) {
            e.minute = n
        }],
        s: [r, function (e, n) {
            e.second = n
        }],
        YYYY: [o, function (e, n) {
            e.year = n
        }],
        S: [/\d/, function (e, n) {
            e.millisecond = n * 100
        }],
        SS: [/\d{2}/, function (e, n) {
            e.millisecond = n * 10
        }],
        SSS: [u, function (e, n) {
            e.millisecond = n
        }],
        d: [r, s],
        ddd: [a, s],
        MMM: [a, m("monthNamesShort")],
        MMMM: [a, m("monthNames")],
        a: [a, function (e, n, t) {
            var r = n.toLowerCase();
            if (r === t.amPm[0]) {
                e.isPm = false
            } else if (r === t.amPm[1]) {
                e.isPm = true
            }
        }],
        ZZ: [/[\+\-]\d\d:?\d\d/, function (e, n) {
            var t = (n + "").match(/([\+\-]|\d\d)/gi), r;
            if (t) {
                r = +(t[1] * 60) + parseInt(t[2], 10);
                e.timezoneOffset = t[0] === "+" ? r : -r
            }
        }]
    };
    D.dd = D.d;
    D.dddd = D.ddd;
    D.DD = D.D;
    D.mm = D.m;
    D.hh = D.H = D.HH = D.h;
    D.MM = D.M;
    D.ss = D.s;
    D.A = D.a;
    n.masks = {
        default: "ddd MMM DD YYYY HH:mm:ss",
        shortDate: "M/D/YY",
        mediumDate: "MMM D, YYYY",
        longDate: "MMMM D, YYYY",
        fullDate: "dddd, MMMM D, YYYY",
        shortTime: "HH:mm",
        mediumTime: "HH:mm:ss",
        longTime: "HH:mm:ss.SSS"
    };
    n.format = function (e, r, u) {
        var o = u || n.i18n;
        if (typeof e === "number") {
            e = new Date(e)
        }
        if (Object.prototype.toString.call(e) !== "[object Date]" || isNaN(e.getTime())) {
            throw new Error("Invalid Date in fecha.format")
        }
        r = n.masks[r] || r || n.masks["default"];
        var a = [];
        r = r.replace(i, function (e, n) {
            a.push(n);
            return "??"
        });
        r = r.replace(t, function (n) {
            return n in g ? g[n](e, o) : n.slice(1, n.length - 1)
        });
        return r.replace(/\?\?/g, function () {
            return a.shift()
        })
    };
    n.parse = function (e, r, u) {
        var o = u || n.i18n;
        if (typeof r !== "string") {
            throw new Error("Invalid format in fecha.parse")
        }
        r = n.masks[r] || r;
        if (e.length > 1e3) {
            return false
        }
        var a = true;
        var i = {};
        r.replace(t, function (n) {
            if (D[n]) {
                var t = D[n];
                var r = e.search(t[0]);
                if (!~r) {
                    a = false
                } else {
                    e.replace(t[0], function (n) {
                        t[1](i, n, o);
                        e = e.substr(r + n.length);
                        return n
                    })
                }
            }
            return D[n] ? "" : n.slice(1, n.length - 1)
        });
        if (!a) {
            return false
        }
        var s = new Date;
        if (i.isPm === true && i.hour != null && +i.hour !== 12) {
            i.hour = +i.hour + 12
        } else if (i.isPm === false && +i.hour === 12) {
            i.hour = 0
        }
        var f;
        if (i.timezoneOffset != null) {
            i.minute = +(i.minute || 0) - +i.timezoneOffset;
            f = new Date(Date.UTC(i.year || s.getFullYear(), i.month || 0, i.day || 1, i.hour || 0, i.minute || 0, i.second || 0, i.millisecond || 0))
        } else {
            f = new Date(i.year || s.getFullYear(), i.month || 0, i.day || 1, i.hour || 0, i.minute || 0, i.second || 0, i.millisecond || 0)
        }
        return f
    };
    if (typeof module !== "undefined" && module.exports) {
        module.exports = n
    } else if (typeof define === "function" && define.amd) {
        define(function () {
            return n
        })
    } else {
        e.fecha = n
    }
})(this);

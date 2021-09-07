
/********************************************* CONFIGURATION **********************************************/


var elements = {
  card: '<section class="card"></section>',
  list: '<section class="card"> <h1 class="title">Links</h1> <section class="list-container"><div class="list"><div class="item headings"> <div class="data">Link</div> <div class="data right created">Created</div> </div></section>  <div class="btns-right"> <a class="btn white prev disabled" href=""><i class="icon only fas fa-angle-left"></i></a> <a class="btn white next disabled" href=""><i class="icon fas fa-angle-right"></i> Next</a> </div> </section>',
  inputs: '<section class="card"> <h1 class="title">Profile</h1> <form> <section class="grey"></section>  <div class="btns-right"><button class="btn"><i class="icon fas fa-save"></i> Save</button></div> </form> </section>',
  error: '<section class="card error"> <i class="error-icon icon fas fa-cat"></i> <h1>No such page exists</h1> <a class="link" href="' + base + '/">Go home <i class="icon fas fa-arrow-right"></i></a> </section>'
}


var delay = (sidebar.find(".menu a").length * 200)





/********************************************* SETUP FUNCTIONS **********************************************/


//Setup app.
var app = Aviation({contentWrapper: ".page", removeFromPath: basePath, source: "a[href]:not([target='_blank']):not(.copying)"})



//Add our menu selection & loading functions.
app.use(function(req, res, next) {
  try {

    //Move to loading state.
    req.started = new Date()
    page.loading()

    //Select menu link.
    if(!req.pathname) { req.pathname = "/" }
    $(".menu a.active").removeClass("active")
    $(".menu a[href='" + base + req.pathname + "']").addClass("active")

    //Create custom content setting function.
    res.html = function(content, after) {
      var wait = delay - (new Date() - req.started)
      if(wait < 1) { wait = 0 }
      setTimeout(function() {
        page.html(content)
        setTimeout(function() { page.loaded(after) }, 50)
      }, wait)

      return req.aviation
    }

    next()
  }
  catch(e) { next(e) }
})





/********************************************* APP FUNCTIONS **********************************************/


/*
Handles rendering the home page.
*/
app.on("/", function(req, res, next) {
  try {
    request("GET", "/home")
    .then(function(data) {
      var charts = renderCharts(data)

      return res.page("Dashboard | " + name).html(charts.html, charts.run)
    })
  }
  catch(e) { next(e) }
})





/*
Handles rendering the links page.
*/
app.on("/links", function(req, res, next) {
  try {
    if(req.query.after) { req.query.after = parseFloat(req.query.after) || 0 }

    request("GET", "/links", {skip: req.query.after, search: req.query.search})
    .then(function(links) {
      if(!links.data.length) {
        var error = $(elements.error)
        error.find(".error-icon").removeClass("fa-cat").addClass("fa-unlink")
        error.find("a").attr("slideover", "#new-link").attr("href", null).attr("tabindex", 0).html('Create one <i class="icon fas fa-arrow-right"></i></a>')
        error.find("h1").text(req.query.search ? "We couldn't find any such link" : "You haven't created any links yet")

        return res.page("Links | " + name).html(error.get(0).outerHTML)
      }

      var card = $(elements.list), list = card.find(".list")
      list.addClass("links")
      if(req.query.search) { card.find(".title").text("Search") }

      for(var i in links.data) {
        var link = links.data[i], redirect = link.redirect.replace(/^https?:\/\//, ""), tag = "", tagType = "success"

        if(link.views) {
          if(link.views > 1000) { tagType = "error" }
          else if(link.views > 100) { tagType = "notice" }
          tag = '<aside class="tag ' + tagType + '">' + safe(commas(link.views)) + ' <i class="icon far fa-eye"></i></aside>'
        }

        list.append('<a class="item" href="' + base + '/links/' + safe(link.id) + '"> <div class="data list-link"> <span class="shortlink" copy="http://' + safe(link.domain) + '/' + safe(link.shortname) + '">' + safe(link.domain) + '/' + safe(link.shortname) + '</span> ' + tag + ' <div>' + safe(redirect) + '</div> </div> <div class="data right created">' + created(link.created) + '</div> </a>')
      }

      if(!req.query.after && !links.has_more) { card.find(".btns-right").remove() }
      else {
        if(req.query.after) { card.find(".prev").attr("href", base + "/links?after=" + (window.lastLinksAfter || 0)).removeClass("disabled") }
        if(links.has_more) { card.find(".next").attr("href", base + "/links?after=" + ((req.query.after || 0) + links.data.length)).removeClass("disabled") }
      }

      window.lastLinksAfter = req.query.after || 0
      res.page("Links | " + name).html(card.get(0).outerHTML)
    })
  }
  catch(e) { next(e) }
})



/*
Handles searches.
*/
$(document).on("submit", ".search", function(e) {
  e.preventDefault()
  app.handle("/links?search=" + encodeURIComponent($(".search input").val()))
})





/*
Handles rendering the link page.
*/
app.on("/links/:id", function(req, res, next) {
  try {
    request("GET", "/link", req.params)
    .then(function(data) {
      var link = data.link, card = $(elements.inputs), form = card.find(".grey")
      form.parents("form").addClass("link-form").attr("link", link.id)
      card.find(".title").html('<span copy="http://' + safe(link.domain) + "/" + safe(link.shortname) + '">' + safe(link.domain) + "/" + safe(link.shortname) + '</span>').append('<div class="btns"> <a class="btn white" tabindex="0" dropdown="#link-dropdown"><i class="icon only fas fa-ellipsis-h"></i></a> </div>')
      $(".delete-link").attr("link", link.id)

      form.append('<div class="group"> <label>Redirect</label> <input class="input" type="text" name="url" placeholder="website.com" value="' + safeInput(link.redirect) + '" spellcheck="false"> </div>')

      var charts = renderCharts(data.views)

      return res.page("Link: " + safe(link.domain) + "/" + safe(link.shortname) + " | " + name).html(card.get(0).outerHTML + charts.html, charts.run)

    })
  }
  catch(e) { next(e) }
})



/*
Handles updating a link.
*/
$(document).on("submit", ".link-form", function(e) {
  e.preventDefault()
  var form = $(this)
  form.parents(".card").addClass("loading")
  request("POST", "/link?id=" + form.attr("link"), {redirect: form.find("input[name='url']").val()})
  .then(function(link) {

    notify("Link has been updated")
    form.parents(".card").removeClass("loading")
  })
})



/*
Handles deleting a link.
*/
$(document).on("click", ".delete-link", function(e) {
  e.preventDefault()
  $('[dropdown="#link-dropdown"]').get(0)._tippy.hide()
  page.loading()
  request("DELETE", "/link?id=" + $(this).attr("link"))
  .then(function(link) {
    app.handle("/links")
    setTimeout(function() {
      notify("Link has been deleted")
    }, 1500)
  })
})





/*
Handles rendering the settings page.
*/
app.on("/settings", function(req, res, next) {
  try {
    var card = $(elements.inputs), form = card.find(".grey")
    card.find(".title").text("Settings")
    form.parents("form").addClass("settings")

    form.append('<div class="group"> <label>Name</label> <input class="input blue" type="text" name="name" placeholder="Linkity" value="' + safeInput(name) + '"> </div>')
    form.append('<div class="group"> <label>Homepage</label> <div class="pretty p-icon p-round p-smooth"><input name="homepage" type="checkbox" ' + (homepage ? "checked" : "") + '><div class="state p-success"><i class="icon fas fa-check"></i><label class="skip">Show</label></div></div> </div>')
    form.append('<div class="group"> <label>Domain</label> <input class="input" type="text" name="domain" placeholder="domain.com" value="' + safeInput(domain) + '" spellcheck="false"> </div>')
    form.append('<div class="group"> <label>Path</label> <input class="input" type="text" name="path" placeholder="/linkity" value="' + safeInput(path) + '" spellcheck="false"> </div>')

    res.page("Settings | " + name).html(card.get(0).outerHTML)
  }
  catch(e) { next(e) }
})



/*
Handles updating the settings.
*/
$(document).on("submit", ".settings", function(e) {
  e.preventDefault()
  var form = $(this)
  form.parents(".card").addClass("loading")
  request("POST", "/settings", {name: form.find("input[name='name']").val(), homepage: form.find("input[name='homepage']").get(0).checked, domain: form.find("input[name='domain']").val(), path: form.find("input[name='path']").val()})
  .then(function(newUser) {
    name = form.find("input[name='name']").val(), homepage = form.find("input[name='homepage']").get(0).checked, domain = form.find("input[name='domain']").val(), path = form.find("input[name='path']").val()

    notify("Settings have been updated")
    form.parents(".card").removeClass("loading")
  })
})





/*
Handles rendering the profile page.
*/
app.on("/profile", function(req, res, next) {
  try {
    var card = $(elements.inputs), form = card.find(".grey")
    card.find(".title").append('<div class="btns"> <a href="' + base + "/profile/sign-out" + '" class="btn white"><i class="icon fas fa-sign-out-alt"></i> Sign out</a> </div>')
    form.parents("form").addClass("profile")

    form.append('<div class="group"> <label>Name</label> <input class="input" type="text" name="name" placeholder="Peter Parker" value="' + safeInput(user.name) + '"> </div>')
    form.append('<div class="group"> <label>Email</label> <input class="input" type="email" name="email" placeholder="pete@oscorp.com" value="' + safeInput(user.email) + '" spellcheck="false"> </div>')
    form.append('<div class="group"> <label>Password</label> <span style="letter-spacing: 3px;">••••••••</span> <a class="link" popup="#change-password" tabindex="0">Change</a> </div>')

    res.page("Profile | " + name).html(card.get(0).outerHTML)
  }
  catch(e) { next(e) }
})



/*
Handles updating the profile.
*/
$(document).on("submit", ".profile", function(e) {
  e.preventDefault()
  var form = $(this)
  form.parents(".card").addClass("loading")
  request("POST", "/profile", {name: form.find("input[name='name']").val(), email: form.find("input[name='email']").val()})
  .then(function(newUser) {
    user.name = newUser.name, user.email = newUser.email

    notify("Profile has been updated")
    form.parents(".card").removeClass("loading")
  })
})



/*
Handles changing the profile.
*/
$(document).on("submit", "#change-password form", function(e) {
  e.preventDefault()
  var form = $(this)
  form.parents(".popup").addClass("loading")
  request("POST", "/profile", {password: form.find("input[name='password']").val()})
  .then(function(newUser) {
    user.name = newUser.name, user.email = newUser.email

    body.removeClass("active")
    form.parents(".popup").removeClass("active").fadeOut(250)
    setTimeout(function() {
      notify("Password has been changed")
      form.parents(".popup").removeClass("loading")
    }, 350)
  })
})





/*
Handles signing out.
*/
app.on("/profile/sign-out", function(req, res, next) {
  try {
    request("POST", "/profile/sign-out")
    .then(function() {
      location.reload()
    })
  }
  catch(e) { next(e) }
})





/*
Handles 404 errors.
*/
app.use(function(req, res, next) {
  try {
    res.page("404 | " + name).html(elements.error)
  }
  catch(e) { next(e) }
})





/********************************************* LINK CREATION FUNCTIONS **********************************************/


/*
Shortens a URL.
*/
$(document).on("submit", "#new-link form", function(e) {
  e.preventDefault()
  var form = $(this)
  form.parents(".slideover").addClass("loading")
  request("POST", "/links", {url: form.find("input[name='url']").val(), shortname: form.find("input[name='shortname']").val()})
  .then(function(link) {
    form.find("input").val("")
    $(".link-wrapper").slideUp(250)
    $(".shortened-link").text(link.domain + "/" + link.shortname).attr("copy", "http://" + link.domain + "/" + link.shortname)
    $(".shortened").slideDown(250)
    page.renderElements()
    setTimeout(function() {
      form.parents(".slideover").removeClass("loading")
    }, 300)
  })
})



/*
Focuses on the shortname input.
*/
$(document).on("click", ".shorten-back", function(e) {
  e.preventDefault()
  $(".shortened").slideUp(250)
  setTimeout(function() {
    $(".link-wrapper").slideDown(250)
    $("#new-link form input[name='url']").focus()
  }, 100)
})



/*
Focuses on the shortname input.
*/
$(document).on("click", ".shortname", function() {
  $(this).find("input").focus()
})



/*
Opens the slideover on N key press.
*/
$(window).keypress(function(e) {
  if($("input:focus").length || e.key.toLowerCase() !== "n") { return }
  $("#new-link").addClass("active")
  setTimeout(function() {
    $("#new-link input[name='url']").focus()
  }, 350)
})





/********************************************* CHARTS RENDERING FUNCTIONS **********************************************/


/*
Renders charts using views object.
*/
function renderCharts(views) {
  var content = $("<span></span>")

  //Create a container for line chart.
  content.append('<div class="card" id="views-chart"><h1 class="title">' + safe(commas(views.count)) + ' views</h1></div>')

  //Create countries table.
  content.append('<div class="card" id="countries"><div class="row">   <section class="col-md-6"> <div class="list-container"><div class="list"> <div class="item headings"> <div class="data">Country</div><div class="data right">Views</div> </div> </div></div> </section>   <section class="col-md-6"> <div class="map"></div> </section>   </div></div>')
  var countriesList = content.find("#countries .list")
  for(var code in views.countries) {
    countriesList.append('<div class="item"> <div class="data">' + safe(countryName[code] || code) + '</div> <div class="data right">' + safe(commas(views.countries[code])) + '</div> </div>')
  }

  //Create browsers, operating systems & devices bar graphs.
  content.append('<div class="row">   <section class="col-md-4"> <div id="browsers" class="card"><h1 class="title">Browsers</h1></div> </section>   <section class="col-md-4"> <div id="os" class="card"><h1 class="title">Operating Systems</h1></div> </section>   <section class="col-md-4"> <div id="devices" class="card"><h1 class="title">Devices</h1></div> </section>   </div>')
  barGraph(content.find("#browsers"), limitObject(views.browsers, 10), {type: "number", suffix: "views"})
  barGraph(content.find("#os"), limitObject(views.operating_systems, 10), {type: "number", suffix: "views"})
  barGraph(content.find("#devices"), limitObject(views.types, 10), {type: "number", suffix: "views"})

  //Create referrers bar graph.
  content.append('<div class="row"> <section class="col-md-3"></section>  <section class="col-md-6"> <div id="referrers" class="card"> <h1 class="title">Referrers</h1> </div> </section>  <section class="col-md-3"></section> </div>')
  var referrers = {}
  for(var domain in views.referrers) { referrers['<img class="favicon" src="https://cdn.staticaly.com/favicons/' + safe(domain) + '"> ' + safe(domain)] = views.referrers[domain] }
  barGraph(content.find("#referrers"), limitObject(referrers, 10), {type: "number", suffix: "views"})


  //Create a function to render the rest on demand.
  var run = function(wrapper) {
    wrapper = $(wrapper || ".page")
    var viewsChart = wrapper.find("#views-chart"), map = wrapper.find("#countries .map"), browsers = wrapper.find("#browsers .bar-graph"), referrers = wrapper.find("#referrers .bar-graph")

    //Builds the views chart.
    chart(viewsChart, views.chart, {type: "number", suffix: "views"})

    //Builds the world map.
    map.vectorMap({
      map: "world_mill",
      backgroundColor: "transparent",
      zoomOnScroll: false,
      focusOn: {
        x: 0.5,
        y: 0.5,
        scale: 2.5
      },
      regionStyle: {
        initial: {
          fill: "#dde4ee",
          "fill-opacity": .9,
          stroke: "none",
          "stroke-width": 0,
          "stroke-opacity": 0
        }
      },
      series: {
        regions: [{
          values: views.countries,
          scale: ["#82a4f6", "#6b90ed", "#5082ff"],
          normalizeFunction: "polynomial"
        }]
      }
    })

    //Runs the animation when the chart is in view.
    var show = function() {
      if(viewsChart.isInView() && !viewsChart.hasClass("active")) { viewsChart.addClass("active") }
      if(map.isInView() && !map.hasClass("active")) {
        map.addClass("active").vectorMap("set", "focus", {x: 0.5, y: 0.5, scale: 1, animate: true})
      }
      if(!browsers.hasClass("active") && browsers.isInView()) {
        browsers.addClass("active")
        barShow("#browsers .bar-graph, #os .bar-graph, #devices .bar-graph")
      }
      if(!referrers.hasClass("active") && referrers.isInView()) {
        referrers.addClass("active")
        barShow("#referrers .bar-graph")
      }
    }
    setTimeout(show, 600)
    $(window).scroll(show)
  }

  return {html: content.html(), run: run}
}





/********************************************* REQUEST FUNCTIONS **********************************************/


/*
Makes a request to the admin API.
*/
function request(method, endpoint, data) {
  var url = base + "/api" + endpoint;

  return new Promise(function(resolve, reject) {
    $.ajax({
      url: url,
      method: method,
      data: data,
      timeout: 15000,
      complete: function(res) {
        if(res.responseJSON && !res.responseJSON.error) { return resolve(res.responseJSON) }
        if(!res.responseJSON) { res.responseJSON = {error: "An internal error occured. Please try again later."} }

        if(res.status == 401) {
          location.reload()
        }
        else {
          notify(res.responseJSON.error)
          $(".loading").removeClass("loading")
          page.loaded()
        }
        reject(res)
      }
    })
  })
}





/********************************************* HELPER FUNCTIONS **********************************************/


/*
Creates the created element for tables.
*/
function created(unix) {
  var m = moment.unix(unix), format = "Do	MMMM YYYY (h:mm A)", date = new Date()
  if(m.isSame(date, "year")) {
    format = "h:mm A, Do	MMMM"
    if(m.isSame(date, "month") && m.isSame(date, "day")) { format = "h:mm A, [Today]" }
  }
  return '<span tooltip="' + m.format(format) + '">' + safe(m.fromNow()) + '</span>'
}



/*
Limits the number of elements of an object.
*/
function limitObject(obj, max) {
  var keys = Object.keys(obj), length = keys.length
  if(length > max) {
    keys = keys.slice(Math.max(keys.length - (length - max), 1))
    for(var i in keys) { delete obj[keys[i]] }
  }
  return obj
}



/*
Checks whether an element is in view
*/
$.fn.isInView = function() {
  var el = $(this), top = el.offset().top, viewable = ($(window).scrollTop() + $(window).height())

  return viewable > (top + 100)
}





/********************************************* LOAD POLYFILLS/DATA **********************************************/


//Promise polyfill.
!function(e,n){"object"==typeof exports&&"undefined"!=typeof module?n():"function"==typeof define&&define.amd?define(n):n()}(0,function(){"use strict";function e(e){var n=this.constructor;return this.then(function(t){return n.resolve(e()).then(function(){return t})},function(t){return n.resolve(e()).then(function(){return n.reject(t)})})}function n(){}function t(e){if(!(this instanceof t))throw new TypeError("Promises must be constructed via new");if("function"!=typeof e)throw new TypeError("not a function");this._state=0,this._handled=!1,this._value=undefined,this._deferreds=[],u(e,this)}function o(e,n){for(;3===e._state;)e=e._value;0!==e._state?(e._handled=!0,t._immediateFn(function(){var t=1===e._state?n.onFulfilled:n.onRejected;if(null!==t){var o;try{o=t(e._value)}catch(f){return void i(n.promise,f)}r(n.promise,o)}else(1===e._state?r:i)(n.promise,e._value)})):e._deferreds.push(n)}function r(e,n){try{if(n===e)throw new TypeError("A promise cannot be resolved with itself.");if(n&&("object"==typeof n||"function"==typeof n)){var o=n.then;if(n instanceof t)return e._state=3,e._value=n,void f(e);if("function"==typeof o)return void u(function(e,n){return function(){e.apply(n,arguments)}}(o,n),e)}e._state=1,e._value=n,f(e)}catch(r){i(e,r)}}function i(e,n){e._state=2,e._value=n,f(e)}function f(e){2===e._state&&0===e._deferreds.length&&t._immediateFn(function(){e._handled||t._unhandledRejectionFn(e._value)});for(var n=0,r=e._deferreds.length;r>n;n++)o(e,e._deferreds[n]);e._deferreds=null}function u(e,n){var t=!1;try{e(function(e){t||(t=!0,r(n,e))},function(e){t||(t=!0,i(n,e))})}catch(o){if(t)return;t=!0,i(n,o)}}var c=setTimeout;t.prototype["catch"]=function(e){return this.then(null,e)},t.prototype.then=function(e,t){var r=new this.constructor(n);return o(this,new function(e,n,t){this.onFulfilled="function"==typeof e?e:null,this.onRejected="function"==typeof n?n:null,this.promise=t}(e,t,r)),r},t.prototype["finally"]=e,t.all=function(e){return new t(function(n,t){function o(e,f){try{if(f&&("object"==typeof f||"function"==typeof f)){var u=f.then;if("function"==typeof u)return void u.call(f,function(n){o(e,n)},t)}r[e]=f,0==--i&&n(r)}catch(c){t(c)}}if(!e||"undefined"==typeof e.length)throw new TypeError("Promise.all accepts an array");var r=Array.prototype.slice.call(e);if(0===r.length)return n([]);for(var i=r.length,f=0;r.length>f;f++)o(f,r[f])})},t.resolve=function(e){return e&&"object"==typeof e&&e.constructor===t?e:new t(function(n){n(e)})},t.reject=function(e){return new t(function(n,t){t(e)})},t.race=function(e){return new t(function(n,t){for(var o=0,r=e.length;r>o;o++)e[o].then(n,t)})},t._immediateFn="function"==typeof setImmediate&&function(e){setImmediate(e)}||function(e){c(e,0)},t._unhandledRejectionFn=function(e){void 0!==console&&console&&console.warn("Possible Unhandled Promise Rejection:",e)};var l=function(){if("undefined"!=typeof self)return self;if("undefined"!=typeof window)return window;if("undefined"!=typeof global)return global;throw Error("unable to locate global object")}();"Promise"in l?l.Promise.prototype["finally"]||(l.Promise.prototype["finally"]=e):l.Promise=t});


//Country names.
var countryName = {"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique"}

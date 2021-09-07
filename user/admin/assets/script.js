
/********************************************* CONFIGURATION **********************************************/


var sidebar = $(".sidebar"), width = $(window).width(), page = $(".page"), body = $("body"), topBar = $(".top-bar"), search = $(".search"), lineCharts = []





/********************************************* SIDEBAR/MENU FUNCTIONS **********************************************/


/*
Opens the sidebar.
*/
sidebar.open = function() {
  sidebar.opened = true
  sidebar.addClass("active")
  if(width <= 768) { sidebar.addClass("mobile-active") }
  var delay = 0;

  setTimeout(function() {
    $(".sidebar .menu a").each(function() {
      var link = $(this)
      delay += 200
      setTimeout(function() { link.addClass("loaded") }, delay)
    })
  }, 350)
}



/*
Closes the sidebar.
*/
sidebar.close = function() {
  var delay = 0;
  $($(".sidebar .menu a").get().reverse()).each(function() {
    var link = $(this)
    delay += 200
    setTimeout(function() { link.removeClass("loaded") }, delay)
  })

  setTimeout(function() { sidebar.removeClass("active").removeClass("mobile-active") }, delay)
}



/*
Handles resizing for menu.
*/
$(window).resize(function() {
  var prevMobile = width <= 768
  width = $(window).width()
  var nowMobile = width <= 768
  if(prevMobile && !nowMobile && sidebar.opened) { sidebar.open() }
  else if(!prevMobile && nowMobile) { sidebar.close() }
})



/*
Handles opening the mobile sidebar.
*/
$(document).on("click", ".menu-icon", function() {
  body.addClass("active")
  sidebar.open()
})



/*
Handles closing the mobile sidebar.
*/
$(document).on("click", function(e) {
  if(sidebar.hasClass("mobile-active") && !has(".sidebar, .menu-icon", e.target)) {
    body.removeClass("active")
    sidebar.close()
  }
})





/********************************************* PAGE FUNCTIONS **********************************************/


/*
Moves the page into loaded state.
+ Generates tooltips, chosen selects & dropdowns.
*/
page.loaded = function(after) {
  page.renderElements()

  //Move to loaded state.
  var delay = (sidebar.find(".menu a").length * 200) + 700
  if(sidebar.opened) { delay = 0 }
  else if(width > 768) { sidebar.open() } else { sidebar.opened = true; delay = 0 }
  setTimeout(function() {
    topBar.addClass("active")
    setTimeout(function() { body.addClass("loading-done") }, 700)
    setTimeout(function() { page.addClass("active") }, 800)
    if(after) { setTimeout(after, 500) }
  }, delay)
}



/*
Moves the page back to loading state.
*/
page.loading = function() {
  var delay = 0
  if(sidebar.hasClass("mobile-active") && width <= 768) { sidebar.close(); delay = sidebar.find(".menu a").length * 200 }
  setTimeout(function() {
    body.removeClass("loading-done")
    page.removeClass("active")
    if($(window).scrollTop()) { setTimeout(function() { $("html, body").animate({scrollTop: 0}, 250) }, 200) }
  }, delay)
}



/*
Renders the elements.
*/
page.renderElements = function() {
  //Create chosen selects.
  $(".chosen").addClass("chosen-created").each(function() {
    $(this).parents(".group").addClass("chosen-group")
  }).chosen()

  //Create copy elements.
  $("[copy]:not(.copy-created)").addClass("copy-created").each(function() {
    var el = $(this)
    tippy(el.get(0), {content: "Click to copy", animation: "scale", theme: "light copy", arrow: true, hideOnClick: false,
    onHidden: function(ins) {
      ins.setContent("Click to copy")
    }})
  })

  //Create tooltips.
  $("[tooltip]:not(.tooltip-created)").addClass("tooltip-created").each(function() {
    var el = $(this), options = {content: el.attr("tooltip"), animation: "scale", theme: "light", arrow: true}
    if(typeof el.attr("follow-cursor") == "string") { options.followCursor = "horizontal" }
    tippy(el.get(0), options)
  })

  //Create dropdowns.
  $("[dropdown]:not(.dropdown-created)").addClass("dropdown-created").each(function() {
    var el = $(this), content = $(el.attr("dropdown")).html()
    tippy(el.get(0), {content: content, trigger: "click", interactive: true, animation: "shift-away", theme: "light dropdown", arrow: true, placement: "bottom-end"})
  })
}





/********************************************* SEARCH FUNCTIONS **********************************************/


/*
Opens the mobile search.
*/
$(document).on("click", ".search-icon", function() {
  body.addClass("active")
  search.addClass("active")
  setTimeout(function() {
    $(".search .input").focus()
  }, 400)
})



/*
Closes the mobile search.
*/
$(document).on("click", function(e) {
  if(search.hasClass("active") && !has(".search, .search-icon", e.target)) {
    body.removeClass("active")
    search.removeClass("active")
  }
})





/********************************************* POPUP FUNCTIONS **********************************************/


/*
Opens the popup on button click.
*/
$(document).on("click", "[popup]", function(e) {
  e.preventDefault()
  body.addClass("active")
  var el = $($(this).attr("popup"))
  el.fadeIn(200).addClass("active")
  setTimeout(function() {
    var input = el.find("input").get(0)
    if(input) { input.focus() }
  }, 350)
})



/*
Closes said popup.
*/
$(document).on("click", function(e) {
  if($(".popup").hasClass("active") && !$(".popup.active").hasClass("loading") && !$(".popup.active").hasClass("undismissable") && !has(".popup, [popup]", e.target)) {
    body.removeClass("active")
    $(".popup.active").removeClass("active").fadeOut(250)
  }
})





/********************************************* SLIDEOVER FUNCTIONS **********************************************/


/*
Opens the slideover on button click.
*/
$(document).on("click", "[slideover]", function(e) {
  e.preventDefault()
  var el = $($(this).attr("slideover"))
  body.addClass("active")
  el.addClass("active")
  setTimeout(function() {
    var input = el.find("input").get(0)
    if(input) { input.focus() }
  }, 350)
})



/*
Closes said slideover.
*/
$(document).on("click", function(e) {
  if($(".slideover").hasClass("active") && !$(".slideover.active").hasClass("loading") && !has(".slideover, [slideover]", e.target)) {
    body.removeClass("active")
    $(".slideover.active").removeClass("active")
  }
})





/********************************************* COPY FUNCTIONS **********************************************/


/*
Adds & removes copying state to list items.
*/
$(document).on("mouseover", "[copy]", function(e) { $(this).parents("a").addClass("copying") })
$(document).on("mouseout", "[copy]", function(e) { $(this).parents("a").removeClass("copying") })



/*
Copies content on copy element click.
*/
$(document).on("click", "[copy]", function(e) {
  e.preventDefault()
  var el = $(this), tippy = el.get(0)._tippy
  copy(el.attr("copy"))
  tippy.setContent("<span class='copied'>Copied</span>")
})



/*
Handles copying to clipboard.
*/
function copy(text, skip) {
  if(navigator.clipboard && navigator.clipboard.writeText && !skip) {
    return navigator.clipboard.writeText(text).catch(function(e) {
      return copy(text, true)
    })
  }
  var input = document.createElement("textarea")
  input.value = text
  document.body.appendChild(input)
  input.focus()
  input.select()
  document.execCommand("copy")
  document.body.removeChild(input)
}





/********************************************* NOTIFICATION FUNCTIONS **********************************************/


/*
Creates, displays & removes a notification.
*/
function notify(message) {
  $(".notify").remove()
  body.append('<div class="notify">' + message + '</div>')
  setTimeout(function() {
    var el = $(".notify")
    el.addClass("active")
    setTimeout(function() {
      el.removeClass("active")
      setTimeout(function() {
        el.remove()
      }, 500)
    }, 3000)
  }, 100)
}





/********************************************* INPUT FUNCTIONS **********************************************/


/*
Handles focusing on input when its icon is clicked.
*/
$(document).on("click", ".input-group .icon, .search .icon", function(e) {
  e.preventDefault()
  $(this).next().focus()
})





/********************************************* JSON FUNCTIONS **********************************************/


/*
Highlights JSON.
*/
JSON.highlight = function(json) {
  if(typeof json !== "string") { json = JSON.stringify(json, 0, 2) }
  json = json.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
  json = json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
    var cls = "number"
    if(/^"/.test(match)) {
      cls = "string"
      if(/:$/.test(match)) {
        cls = "key"
        match = match.slice(0, -1) + "</span>:<span>"
      }
    }
    else if(/true|false/.test(match)) { cls = "boolean" }
    else if(/null/.test(match)) { cls = "null" }
    return '<span class="' + cls + '">' + match + "</span>"
  })
  var lines = json.split("\n"), result = "", line = 0
  for(var i in lines) {
    if(!lines[i]) { continue }
    line++
    result += '\n<span class="line">' + line + '</span>' + lines[i]
  }

  return result
}





/********************************************* CHARTING FUNCTIONS **********************************************/


/*
Creates a line chart for time series data.
*/
function chart(element, data, tooltip, reloaded) {
  var svg = $(element).find("svg")
  if(!svg.length) { svg = $(element).addClass("chart").append('<div class="chart-container"><svg></svg></div>').find("svg") }
  var height = svg.height(), width = svg.width()

  //Get max & min value.
  var min = 0, max = 0
  for(var time in data) {
    if(!min || data[time] < min) { min = data[time] }
    if(data[time] > max) { max = data[time] }
  }
  if(max <= 0) { return }

  //Draw chart specs.
  var linePath = "M0," + height, pointsWidth = width / Object.keys(data).length, i = 0, points = []
  for(var time in data) {
    var val = data[time]
    if(typeof val == "undefined" || val === null) { continue }
    i++
    var point = {height: (height - ((val / max) * height)), width: pointsWidth * i, time: time, value: val}
    points.push(point)
    linePath += ("L" + point.width + "," + point.height)
  }
  svg.attr("points", JSON.stringify(points))

  //Add lines to the SVG.
  svg.html("")
  svg.append('<path class="bg-layer" d="' + (linePath + "L" + point.width + "," + height) + '"></path>')
  svg.append('<path class="line-layer" d="' + linePath + '"></path>')
  svg.append('<circle class="circle-layer" cx="' + point.width + '" cy="' + point.height + '" r="6" />')

  //Refresh parent element.
  var parent = $(svg.parent())
  parent.html(parent.html())

  if(!reloaded) {
    lineCharts.push({el: parent, data: data})
    if(tooltip) { chartTooltip(parent, tooltip) }
  }
  return points
}



/*
Refreshes charts on resize.
*/
$(window).resize(function() {
  for(var i in lineCharts) {
    chart(lineCharts[i].el, lineCharts[i].data, null, true)
  }
})



/*
Creates tooltips for charts.
*/
function chartTooltip(el, type) {
  if(!type) { type = "number" }
  else if(typeof type == "object") { var suffix = type.suffix, type = type.type } else { var suffix = "" }
  el = $(el)
  if(el.find(".chart-container").length) { el = el.find(".chart-container") }

  var tip = tippy(el.get(0), {followCursor: "horizontal", hideOnClick: false, interactive: true, animation: "scale", theme: "light", arrow: true})
  tip = tip.targets._tippy

  //Create data points.
  var data = [], getPoints = function() {
    var points = JSON.parse(el.find("svg").attr("points")), prevWidth = 0, width = points[points.length - 1].width / points.length
    data = []
    for(var i in points) {
      var point = points[i], value = point.value

      if(type == "percentage") { value = parseFloat(value.toFixed(2)) + "%" }
      else { value = commas(value) }
      if(suffix) { value += " " + suffix }

      data.push({start: prevWidth, end: prevWidth + width, html: '<div class="time-tooltip"> <div class="time">' + safe(point.time) + '</div> <div class="value">' + safe(value) + '</div> </div>'})
      prevWidth += width
    }
  }
  getPoints()
  $(window).resize(getPoints)

  //Listen for hover.
  el.on("mouseover mouseenter mousemove touchmove", function(e) {
    var pos = (e.offsetX - 10), html = false
    for(var i in data) { if(pos >= data[i].start && pos <= data[i].end) { html = data[i].html } }
    if(!html) { return }

    tip.setContent(html)
  })
}





/********************************************* BAR GRAPH FUNCTIONS **********************************************/


/*
Draws the bar graph.
*/
function barGraph(el, data, tooltip) {
  if(typeof tooltip == "string") { tooltip = {type: tooltip} }
  el = $(el)
  var graph = $('<div class="bar-graph"></div>')

  //Process data & find out max value.
  var finalData = [], max = 0
  for(var label in data) {
    var val = data[label], tip = commas(val)
    if(tooltip.type == "percentage") { tip = parseFloat(val.toFixed(2)) + "%" }
    if(tooltip.suffix) { tip += " " + tooltip.suffix }

    finalData.push({
      label: label,
      value: val,
      tooltip: tip
    })

    if(val > max) { max = val }
  }
  finalData.sort(function(a, b) {
    if(a.value > b.value) { return -1 }
    if(b.value > a.value) { return 1 }
  })

  //Add data to graph.
  for(var i in finalData) {
    var data = finalData[i]
    graph.append('<div class="line" tooltip="' + safe(data.tooltip) + '" follow-cursor> <label>' + data.label + '</label> <span style="max-width: ' + safe(data.value / max * 100) + '%;"></span> </div>')
  }

  el.append(graph.get(0).outerHTML)
  return graph
}



/*
Displays the bar graph.
*/
function barShow(el) {
  el = $(el)
  var delay = 0
  el.find(".line").each(function() {
    var sp = $(this)
    setTimeout(function() { sp.addClass("active") }, delay)
    delay += 450
  })
}





/********************************************* HELPER FUNCTIONS **********************************************/


/*
Checks if an event contains a class.
*/
function has(classes, target) {
  classes = classes.split(",");
  for(var key in classes) {
    var selector = classes[key].trim()
    if($(target).closest(selector).length !== 0) {
      return true
    }
  }
  return false
}



/*
Adds commas to numbers.
*/
function commas(num) {
  return String(num || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}



/*
Escapes text for safe usage.
*/
function safe(text) {
  if(!text) { return "" }
  text = String(text).replace(/\&/gi, "&amp;").replace(/\</gi, "&lt;").replace(/\>/gi, "&gt;").replace(/\"/gi, "&quot;").replace(/\'/gi, "&#x27;").replace(/\//gi, "&#x2F;")
  return text
}



/*
Escapes input values for safe usage.
*/
function safeInput(text) {
  if(!text) { return "" }
  text = String(text).replace(/\</gi, "&lt;").replace(/\>/gi, "&gt;").replace(/\"/gi, "&quot;").replace(/\'/gi, "&#x27;")
  return text
}

!function ($) {


  var today = moment();

  function Calendar(selector, events) {
    this.el = document.querySelector(selector);
    this.events = events;
    this.current = moment().date(1);
    this.draw();
    var current = document.querySelector('.today');
    if (current) {
      var self = this;
      window.setTimeout(function () {
        self.openDay(current);
      }, 500);
    }
  }

  Calendar.prototype.draw = function () {
    this.drawHeader();

    this.drawMonth();

    this.drawLegend();
  }

  Calendar.prototype.drawHeader = function () {
    var self = this;
    if (!this.header) {
      this.header = createElement('div', 'motce_calendar_header');
      this.header.className = 'motce_calendar_header';

      this.title = createElement('h1');

      var right = createElement('div', 'motce_calendar_header_right_arrow');
      right.addEventListener('click', function () { self.nextMonth(); });

      var left = createElement('div', 'motce_calendar_header_left_arrow');
      left.addEventListener('click', function () { self.prevMonth(); });

      this.header.appendChild(this.title);
      this.header.appendChild(right);
      this.header.appendChild(left);
      this.el.appendChild(this.header);
    }

    this.title.innerHTML = this.current.format('MMMM YYYY');
  }

  Calendar.prototype.drawMonth = function () {
    var self = this;
    currentMonth = self.current._d.getMonth();
    this.events.forEach(function (ev) {
      eventDate = new Date(ev.startDate);
      eventDay = eventDate.getDate();
      eventMonth = eventDate.getMonth();

      if (currentMonth === eventMonth)
        ev.date = self.current.clone().date(eventDay);
    });

    if (this.month) {
      this.oldMonth = this.month;
      this.oldMonth.className = 'motce_calendar_month out ' + (self.next ? 'next' : 'prev');
      this.oldMonth.addEventListener('webkitAnimationEnd', function () {
        self.oldMonth.parentNode.removeChild(self.oldMonth);
        self.month = createElement('div', 'motce_calendar_month');
        self.backFill();
        self.currentMonth();
        self.fowardFill();
        self.el.appendChild(self.month);
        window.setTimeout(function () {
          self.month.className = 'motce_calendar_month in ' + (self.next ? 'next' : 'prev');
        }, 16);
      });
    } else {
      this.month = createElement('div', 'motce_calendar_month');
      this.el.appendChild(this.month);
      this.backFill();
      this.currentMonth();
      this.fowardFill();
      this.month.className = 'motce_calendar_month new';
    }
  }

  Calendar.prototype.backFill = function () {
    var clone = this.current.clone();
    var dayOfWeek = clone.day();

    if (!dayOfWeek) { return; }

    clone.subtract('days', dayOfWeek + 1);

    for (var i = dayOfWeek; i > 0; i--) {
      this.drawDay(clone.add('days', 1));
    }
  }

  Calendar.prototype.fowardFill = function () {
    var clone = this.current.clone().add('months', 1).subtract('days', 1);
    var dayOfWeek = clone.day();

    if (dayOfWeek === 6) { return; }

    for (var i = dayOfWeek; i < 6; i++) {
      this.drawDay(clone.add('days', 1));
    }
  }

  Calendar.prototype.currentMonth = function () {
    var clone = this.current.clone();

    while (clone.month() === this.current.month()) {
      this.drawDay(clone);
      clone.add('days', 1);
    }
  }

  Calendar.prototype.getWeek = function (day) {
    if (!this.week || day.day() === 0) {
      this.week = createElement('div', 'motce_calendar_week');
      this.month.appendChild(this.week);
    }
  }

  Calendar.prototype.drawDay = function (day) {
    var self = this;
    this.getWeek(day);

    var outer = createElement('div', this.getDayClass(day));
    outer.addEventListener('click', function () {
      self.openDay(this);
    });

    var name = createElement('div', 'motce_calendar_day-name', day.format('ddd'));

    var number = createElement('div', 'motce_calendar_day-number', day.format('DD'));


    var events = createElement('div', 'motce_calendar_day-events');
    this.drawEvents(day, events);

    outer.appendChild(name);
    outer.appendChild(number);
    outer.appendChild(events);
    this.week.appendChild(outer);
  }

  Calendar.prototype.drawEvents = function (day, element) {
    if (day.month() === this.current.month()) {
      var todaysEvents = this.events.reduce(function (memo, ev) {
        if (ev.date && ev.date.isSame(day, 'day')) {
          memo.push(ev);
        }
        return memo;
      }, []);

      todaysEvents.forEach(function (ev) {
        var evSpan = createElement('span', ev.color);
        element.appendChild(evSpan);
      });
    }
  }

  Calendar.prototype.getDayClass = function (day) {
    classes = ['motce_calendar_day'];
    if (day.month() !== this.current.month()) {
      classes.push('other');
    } else if (today.isSame(day, 'day')) {
      classes.push('today');
      classes.push('active');
    }
    return classes.join(' ');
  }

  Calendar.prototype.openDay = function (el) {
    var details, arrow;
    var dayNumber = +el.querySelectorAll('.motce_calendar_day-number')[0].innerText || +el.querySelectorAll('.motce_calendar_day-number')[0].textContent;
    var day = this.current.clone().date(dayNumber);

    var currentOpened = document.querySelector('.motce_details');

    let active_classes = document.getElementsByClassName('active');
    
    if(active_classes.length > 0)
      active_classes[0].classList.remove('active');

    el.classList.add('active');

    if (currentOpened && currentOpened.parentNode === el.parentNode) {
      details = currentOpened;
      arrow = document.querySelector('.motce_arrow');
    } else {

      if (currentOpened) {
        currentOpened.addEventListener('webkitAnimationEnd', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('oanimationend', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('msAnimationEnd', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('animationend', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.className = 'motce_details out';
      }

      details = createElement('div', 'motce_details in');

      el.parentNode.appendChild(details);
    }

    var todaysEvents = this.events.reduce(function (memo, ev) {
      if (ev.date && ev.date.isSame(day, 'day')) {
        memo.push(ev);
      }
      return memo;
    }, []);

    this.renderEvents(todaysEvents, details);

  }

  Calendar.prototype.renderEvents = function (events, ele) {
    var currentWrapper = ele.querySelector('.motce_events');
    var wrapper = createElement('div', 'motce_events in' + (currentWrapper ? ' new' : ''));

    events.forEach(function (ev) {
      var div = createElement('div', 'motce_event');
      var square = createElement('div', 'motce_event-category ' + ev.color);
      var a = createElement('a', 'motce_event-link', ev.eventName);
      a.href = ev.webLink;
      a.target = '_blank';

      div.appendChild(square);
      div.appendChild(a);
      wrapper.appendChild(div);
    });

    if (!events.length) {
      var div = createElement('div', 'motce_event empty');
      var span = createElement('span', '', 'No Events');

      div.appendChild(span);
      wrapper.appendChild(div);
    }

    if (currentWrapper) {
      currentWrapper.className = 'motce_events out';
      currentWrapper.addEventListener('webkitAnimationEnd', function () {
        currentWrapper.parentNode.removeChild(currentWrapper);
        ele.appendChild(wrapper);
      });
      currentWrapper.addEventListener('oanimationend', function () {
        currentWrapper.parentNode.removeChild(currentWrapper);
        ele.appendChild(wrapper);
      });
      currentWrapper.addEventListener('msAnimationEnd', function () {
        currentWrapper.parentNode.removeChild(currentWrapper);
        ele.appendChild(wrapper);
      });
      currentWrapper.addEventListener('animationend', function () {
        currentWrapper.parentNode.removeChild(currentWrapper);
        ele.appendChild(wrapper);
      });
    } else {
      ele.appendChild(wrapper);
    }
  }

  Calendar.prototype.drawLegend = function () {
    var legend = createElement('div', 'motce_legend');
    var calendars = this.events.map(function (e) {
      return e.calendar + '|' + e.color;
    }).reduce(function (memo, e) {
      if (memo.indexOf(e) === -1) {
        memo.push(e);
      }
      return memo;
    }, []).forEach(function (e) {
      var parts = e.split('|');
      var entry = createElement('span', 'motce_entry ' + parts[1], parts[0]);
      legend.appendChild(entry);
    });
    this.el.appendChild(legend);
  }

  Calendar.prototype.nextMonth = function () {
    this.current.add('months', 1);
    this.next = true;
    this.draw();
  }

  Calendar.prototype.prevMonth = function () {
    this.current.subtract('months', 1);
    this.next = false;
    this.draw();
  }

  window.Calendar = Calendar;

  function createElement(tagName, className, innerText) {
    var ele = document.createElement(tagName);
    if (className) {
      ele.className = className;
    }
    if (innerText) {
      ele.innderText = ele.textContent = innerText;
    }
    return ele;
  }
}();

!function () {

  calendarEmbedHandleBackendCalls('motce_get_all_events', { 'upn': embedConfig.upnID }).then((res) => {
    if (!res.success) {
      jQuery('#calendar_loader').show()
      jQuery('#calendar_loader').html(res.data.Description);
      return;
    }

    events = res.data;

    calendarEmbedHandleBackendCalls('motce_get_all_outlook_categories', { 'upn': embedConfig.upnID }).then((res) => {

      if (!res.success) {
        jQuery('#calendar_loader').show()
        jQuery('#calendar_loader').html(res.data.Description);
        return;
      }

      categories = res.data;
      categories.Event = { color: 'presetdefault' };
      console.log(categories);

      events.forEach(event => {
        event.color = "motce_"+categories[event.calendar].color;
      });

      let calendar = new Calendar('#motce_calendar', events);
    });


  });

  function calendarEmbedHandleBackendCalls(task, payload, loader = 'calendar_loader') {

    return jQuery.ajax({
      url: `${embedConfig.ajax_url}?action=motce_calendar_embed&nonce=${embedConfig.nonce}`,
      type: "POST",
      data: {
        task,
        payload
      },
      cache: false,

      beforeSend: function () {

        jQuery(`#${loader}`).show();

      },
      success: function (data) {
        jQuery(`#${loader}`).hide();
        return data;
      },
    });

  }

  
  document.querySelectorAll('.dropdown-toggle').forEach(dropDownFunc);

  function dropDownFunc(dropDown) {
      console.log(dropDown.classList.contains('click-dropdown'));

      if(dropDown.classList.contains('click-dropdown') === true){
          dropDown.addEventListener('click', function (e) {
              e.preventDefault();        
      
              if (this.nextElementSibling.classList.contains('dropdown-active') === true) {
                  this.parentElement.classList.remove('dropdown-open');
                  this.nextElementSibling.classList.remove('dropdown-active');
      
              } else {
                  closeDropdown();
                  this.parentElement.classList.add('dropdown-open');
                  this.nextElementSibling.classList.add('dropdown-active');
              }
          });
      }
  };

  window.addEventListener('click', function (e) {
      if (e.target.closest('.dropdown-container') === null) {
          closeDropdown();
      }

  });

  function closeDropdown() { 
      console.log('run');
      document.querySelectorAll('.dropdown-container').forEach(function (container) { 
          container.classList.remove('dropdown-open')
      });

      document.querySelectorAll('.dropdown-menu').forEach(function (menu) { 
          menu.classList.remove('dropdown-active');
      });
  }

  document.querySelectorAll('.dropdown-menu').forEach(function (dropDownList) { 
      dropDownList.onmouseleave = closeDropdown;
  });


}(jQuery);

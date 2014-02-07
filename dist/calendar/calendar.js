$(function() {
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	var calendar = $('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		monthNames: ['Январь','Февраль','Март','Апрель','Май','οюнь','οюль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв.','Фев.','Март','Апр.','Май','οюнь','οюль','Авг.','Сент.','Окт.','Ноя.','Дек.'],
		dayNames: ["Воскресенье","Понедельник","Вторник","Среда","Четверг","Пятница","Суббота"],
		dayNamesShort: ["ВС","ПН","ВТ","СР","ЧТ","ПТ","СБ"],
		buttonText: {prev: "&nbsp;&#9668;&nbsp;",next: "&nbsp;&#9658;&nbsp;",prevYear: "&nbsp;&lt;&lt;&nbsp;",nextYear: "&nbsp;&gt;&gt;&nbsp;",today: "Сегодня",month: "Месяц",week: "Неделя",day: "День"},
		selectable: true,
		selectHelper: true,
		select: function(start, end, allDay) {
			var select_time = Math.round(start.getTime()/1000);
			$('#ajaxModal').modal({remote: "/manage_game/add?ajax=1&time="+select_time, refresh: true});
			calendar.fullCalendar('unselect');
		},
		editable: false,
		timeFormat: 'H(:mm)',
		eventRender: function(calev, elt, view) {
			/*var today = new Date();
			if (calev.start.getTime() < today.getTime()){
				elt.addClass("alert-success");
				//elt.children().addClass("past");
			}else{
				//elt.addClass("label-primary");
				elt.addClass("alert-success");
			}*/
		},
		eventClick: function(calEvent, jsEvent, view) {
			//console.log(calEvent);
			$('#ajaxModal').modal({remote: '/manage_game/edit/'+calEvent.id+'?ajax=1', refresh: true});
		},
		events: '/manage_game/json_events'
	});

});
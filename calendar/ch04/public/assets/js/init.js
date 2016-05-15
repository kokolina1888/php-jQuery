'use strict'; //enforce variable declaration - safer coding

//makes sure the document is ready before executing scripts
jQuery(function($){
console.log("init.js was loaded successfully.");

	//file to which Ajax requests should be sent
	var processFile="assets/inc/ajax.inc.php";

	//Functions to manipulate the modal window
	var fx = {
		//checks for a modal window and returns it, or
		//else creates a new one and returns tht
		"initModal": function(){
			//if no elements are matched, the length
			//property will return 0
			if($(".modal-window").length == 0){
				//creates a div, adds a class, and
				//appends it to the body tag
				return $('<div>')
				.hide()
				.addClass("modal-window")
				.appendTo("body");
			} else {
				//returns the modal window if one
				//already exists in the DOM
				return $(".modal-window");
			}
		},
		'boxin': function(data, modal){
			//creates an overlay for the site, adds
			//a class and a click event handler, the
			//appends it to the body element
			$("<div>")
			.hide()
			.addClass('modal-overlay')
			.click(function(event){
					//removes event
					fx.boxout(event);
				})
			.appendTo('body');
			//loads the data into themodal window and
			//appends it to the body element
			modal
			.hide()
			.append(data)
			.appendTo('body');

			//fades in the modal window and overlay
			$('.modal-window, .modal-overlay')
			.fadeIn('slow');

		},
		"boxout": function(event){
			//if an event was triggered by theelement
			//that called this function, prevents the
			//default action from firing

			if (event!=undefined) {
				event.preventDefault();
			}

			//removes the active class from all links
			$("a").removeClass("active");
			
			//fades out the modal window, 
			//then removes it from the DOM entirely
				$(".modal-window, .modal-overlay")
				.fadeOut('slow', function(){
					$(this).remove();
				});
			},
		'addevent': function(data, formData){
			//converts the query string to an object
			var entry = fx.deserialize(formData),

			//makes a date obj for current month
			cal = new Date(NaN),

			//makes a date obj for the new event
			event = new Date(NaN),

			//extracts the calendar month fro the h2 id
			cdata = $('h2').attr('id').split('-'),

			//extracts the event day, month and year
			date = entry.event_start.split(' ')[0],

			//splits the event data into pieces
			edata = date.split('-');

			//sets the date for the calendar date object
			cal.setFullYear(cdata[1], cdata[2], 1);

			//sets the date for the event date object
			event.setFullYear(edata[0], edata[1], edata[2]);

			//since the date object is created using
			//gmt, then adjusted for the local time zone,
			//adjust the offset to ensure a proper date
			event.setMinutes(event.getTimezoneOffset());

			//if the year and month match start the process
			//of adding the new event to the calendar

			if( cal.getFullYear()==event.getFullYear() && cal.getMonth()==event.getMonth() )
			{
				//gets the day of the month for event
				var day = String(event.getDate());

				//adds a leading zero to 1-digit days
				day = day.length == 1? "0"+day:day;

				//adds the new date link
				$('<a>')
					.hide()
					.attr('href', 'view.php?event_id='+data)
					.text(entry.event_title)
					.insertAfter($('strong:contains('+day+')'))
					.delay(1000)
					.fadeIn('slow');
			}
			},
		'removeevent': function()
		{
			//removes an event from the markup after deletion

			$('.active')
				.fadeOut('slow', function(){
					$(this).remove();
				});
		},
		'deserialize': function(str){
			//breaks apart each name-value pair
			var data = str.split('&'),

			//declares var for use in the loop
			pairs = [], entry = {}, key, val;

			//loops through each name-value pair

			for ( var x in data )
				{
					//splits each pair into an array
					pairs = data[x].split('=');
	
					//the first element is the name
					key = pairs[0];
	
					//second element is the value
					val = pairs[1];
	
					//stores each value as an object property
					entry[key] = val;
	
					//reverses the URL encoding and stores
					//each value as an object
					entry[key] = fx.urldecode(val);
	
			}
		return entry;
		},

		//decodes a query string value
		'urldecode': function(str){
			
			//converts plus signs to spaces
			var converted = str.replace(/\+/g, ' ');

			//converts any encoded entitles back
			return decodeURIComponent(converted);
			}			
		};

		$('body').on("click", "li>a", function(event){
		//stops the link from loading view
		event.preventDefault();

		$(this).addClass('active');
		
		//gets the query string from the link href
		var data = $(this)
		.attr('href')
		.replace(/.+?\?(.*)$/, "$1");

			//checks if the modal window exists and
			//selects it, or creates a new one
			var modal = fx.initModal();

		//creates a button to close the window

		$("<a>")
		.attr("href", "#")
		.addClass("modal-close-btn")
		.html("&times;")
		.click(function(event){
				//removes the modal window
				fx.boxout(event);
			})
		.appendTo(modal);

			//Loads the event data from db
			$.ajax({
				type: "POST",
				url: processFile,
				data: "action=event_view&" + data,
				success: function(data){
				//Alertevent data for now
				fx.boxin(data, modal);
			},
			error: function(msg){
				modal.append(msg);
			}

		});
		});

		//displays the edit form as a modal window
		$('body').on('click', '.admin-options form, .admin', function(event){
			//prevents the form from submitting
			event.preventDefault();

			var modal = fx.initModal();
			var action = $(event.target).attr('name') || "edit_event";
			var targ = $(event.target).siblings("[name='event_id']").val();
			
			console.log(targ);

			//saves the value of the event_id input
			var id = $(event.target)
					.siblings("[name='event_id']")
					.val();

			//creates an additional param for the id if set
			id = (id!=undefined) ? "&event+id="+id : "";
			$.ajax({
				type: "POST",
				url: processFile,
				data: "action="+action+id,
				success: function(data){
					var form = $(data).hide(),

					//make sure the modal window exists
					modal = fx.initModal()
							.children(":not(.modal-close-btn)")
							.remove()
							.end();

					fx.boxin(null, modal);
					form
						.appendTo(modal)
						.addClass('edit-form')
						.fadeIn('slow');
				},
				error: function(msg){
					alert(msg);
				}
			});//end ajax
		});//end func

		//make the cancel button on editting forms behave like the 
		//close button and fade out modal windows and overlays
		$('body').on('click', 'edit-form a:contains(cancel)', function(event){
			fx.boxout(event);
		});


		//edits event without reloading
		$("body").on("click", ".edit-form input[type=submit]", function(event){
        // Prevents the default form action from executing
       		event.preventDefault();

       		//if editing existing event, need to pay attention to the title

       		if( $(this).attr('name')=='event_submit' && $('.active').length > 0)
		{
			//need to check if the event title has been changed
			//here`s the title that`qs on the main calendar page=

			var oldTitle = $('.active')[0].innerHTML;

			//here we fish out the (possibly) different title from the form data.

			var formArray = $(this).parents('form').serializeArray();
			var titleArray = $.grep(formArray, function(elem){
				return elem.name === 'event_title';
				});

				var newTitle = titleArray.length >0 ? titleArray[0].value:'';
				if(newTitle !== oldTitle)
				{
					//the event title has been changed, so update the page

					$('.active')[0].innerHTML = newTitle;
				}
			
		}
       		var formData = $(this).parents('form').serialize(),

       		//stores the value of the submit button
       		submitVal = $(this).val(),

       		//determines if the event should be removed
       		remove = false,

       		//saves the start date input string
       		start = $(this).siblings("[name=event_start]").val(),

       		//saves the end date input string
       		end = $(this).siblings("[name=event_end]").val();


       		//if this is the deletion form, appends an action
       		if ($(this).attr('name') == 'confirm_delete')
       		{
       			//adds necessary info to the query string
       			formData += "&action=confirm_delete"+"&confirm_delete=" + submitVal;

       			//if the event is really being deleted, sets
       			// a flag to remove it from the markup

       			if( submitVal == "Yes, Delete It")
       			{
       				remove = true;
       			}
       		}

       		//if creating/editing an event, checks for valid dates
       		if( $(this).siblings("[name=action]").val()=="event_edit"){
       			if (!validDate(start) || !validDate(end)) {
       				alert("Valid dates only! (YYYY-MM-DD HH:MM:SS)");
       				return false;
       			}
       		}
       		$.ajax({
       			type: "POST",
       			url: processFile,
       			data: formData,
       			success: function(data){

       				//if this is a deleted event, removes
       				//it from the markup

       			if(remove === true)
       			{
       				fx.removeevent();
       			}
       				fx.boxout();

       				//if this is a new event, adds it to
       				//the calendar
       				if ($("[name=event_id]").val().length==0 && remove === false) 
       			{

       				//adds the event to the calendar
       				fx.addevent(data, formData);
       			}
       				
       				},
       			error: function(msg){
       				alert(msg);
       			}
       		});
			
		});	
			

//logs the query string
//console.log(data);			
//proves the event handler worked by logging the link text
//console.log($(this).text());

});
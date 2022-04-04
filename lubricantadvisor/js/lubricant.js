// Make the browse labels translatable by javascript
var browseLabels = {
  familygroup: "Choose a Sector",
  family: "Choose a Category",
  manufacturer: "Choose a Make",
  series: "Choose a Model Series",
  year: "Choose a Year",
  equipment: "Choose a Equipment",
  alt_fueltype: "Choose a Fuel Type",
  fueltype: "Choose a Fuel Type",
  displacement: "Choose Engine Size",
};

var alwaysShow = "";

var show_button = "";
var $ = jQuery;
$(function () {
	
	
  /* autocomplete */
    //alert('$("#edit-q").val()'+$("#edit-q").val());
    if($("#edit-q").val() == ''){
		 $("#lubadvisorautocomp").empty(); 
	}
	
	$("#edit-q").keyup(function() {
		if (!this.value) {
			//alert('The box is empty');
			$("#lubadvisorautocomp").empty(); 
		}else {
			lubricantadvisorautocomplete();
		}
	});
	
	
	function lubricantadvisorautocomplete(){
		$("#edit-q").autocomplete({		
			appendTo: $("#edit-q").parent(),
			select: function(event, ui){
				//console.log(ui.item.url)
				// redirect to url            
				window.location ='/'+ui.item.url;
			},
			response: function(event, ui) {
				if (ui.content.length === 0) {
					$("#lubadvisorautocomp").text("No results found");
				} else {
					$("#lubadvisorautocomp").empty();
				}
			},
			autoFocus: true,
		});
		
	}
    $("#lubadvisorautocomp").empty();  
	/*$("#edit-q").autocomplete({		
		appendTo: $("#edit-q").parent(),
		select: function(event, ui){
            //console.log(ui.item.url)
            // redirect to url            
			window.location ='/'+ui.item.url;
		},
		response: function(event, ui) {
            if (ui.content.length === 0) {
                $("#lubadvisorautocomp").text("No results found");
            } else {
                $("#lubadvisorautocomp").empty();
            }
        },
		autoFocus: true,
	});*/

    $("#edit-q").keydown(function(event){
		if (!this.value) {			
			$("#lubadvisorautocomp").empty(); 
		}
		if(event.keyCode == 13) {
			if($("#edit-q").val().length==0) {
			  event.preventDefault();
			  return false;
			}
			else {
				//alert('enter key autocomplete');
				
				lubricantadvisorautocomplete();				
				event.preventDefault();return false;
				
			}
		}
	 });
	
  /* autocomplete */
  function equipmentPathMethod() {
    if (show_button == true) {
      // only go to the equipment page when the button is pressed
      $("#equipment_button").click(function () {
        var browse_path = $(this)
          .parent(".dropDownSelectors")
          .prev()
          .children("select")
          .children("option:selected")
          .val();
        var path = $("select#equipment").val();
        var url = path + "?browse_path=" + browse_path;
        window.location = url;
      });
    } else {
      // load the equipment path handler so the equipment page loads when something is chosen
      $(".ajax-dropdowns select#equipment").change(function () {
        var browse_path = $(this)
          .parent(".dropDownSelectors")
          .prev()
          .children("select")
          .children("option:selected")
          .val();
        var path = $("select#equipment").val();
        var url = path + "?browse_path=" + browse_path;
        window.location = url;
      });
    }
  }

  // Compare the steps already displayed by the API to the list of steps to always show
  function getAlwaysShow(browse) {
    var fromApi = Object.keys(browse);
    for (var i = 0; i < alwaysShow.length; ++i) {
      if (fromApi.indexOf(alwaysShow[i]) === -1) {
        return alwaysShow.slice(i);
      }
    }
    // If everything is showing, or nothing left in the always show list, return nothing
    return [];
  }

  $(document).ready(function () {
	  
/* Olyslager API integration js code */	  
	 $('.sectorIcon .sectorIconImage').on('click', function () {
		//alert('click');
		//alert($(this).find("a").attr("data-id"));
		//alert($(this).find("a").attr("data-path"));
	
		var path = $(this).find("a").attr("data-path");
		var category_id = $(this).find("a").attr("data-id");
		$("input[name=category_id]").val('');
		$("input[name=make_id]").val('');
		$("input[name=model_id]").val('');
		$("input[name=type_id]").val('');
		if(category_id != ''){
			renderMakeDropDown(path,category_id);
		}		
	 });
	 
	 $('.sectorIcon .sectorIconText').on('click', function () {
		//alert('click');
		//alert($(this).attr("data-id"));
		//alert($(this).attr("data-path"));
	
		var path = $(this).attr("data-path");
		var category_id = $(this).attr("data-id");
		$("input[name=category_id]").val('');
		$("input[name=make_id]").val('');
		$("input[name=model_id]").val('');
		$("input[name=type_id]").val('');
		if(category_id !=''){
			renderMakeDropDown(path,category_id);
		}
		
	 });
	  
	 function renderMakeDropDown(path,category_id){
		 console.log('url==='+path); 
		 //alert('category_id'+category_id);
		 $("input[name=category_id]").val(category_id);
		  // for the loading gif
		  var loading = $("#loading");
		  // when an ajax request is made, show the laoding gif
		  $(document).ajaxStart(function () {
			loading.show();
		  });
		  // when the ajax call stops, hide the loading gif
		  $(document).ajaxStop(function () {
			loading.hide();
		  });
		 loading.show();
		 jQuery.ajax({
			  url: Drupal.url(path),
			  type: "POST",			  
			  dataType: "json",
			  beforeSend: function(x) {
				if (x && x.overrideMimeType) {
				  x.overrideMimeType("application/json;charset=UTF-8");
				}
			  },
			  success: function(result) {
				loading.hide();	
			
				 if(result.status == 200){
					
					 if (typeof result.category != "undefined" && result.category != null && result.category.length != null && result.category.length > 0) {
							// array exists and is not empty
							$(".ajax-dropdowns select#" + "edit-manufacturer").empty();
							$.each(result.category, function (index, value) {					
								$.each(value, function (i, val) {					
									//console.log('index=='+index);
									if(index == 0){
										$(".ajax-dropdowns select#" + "edit-manufacturer").append(
											  $("<option/>")
												.attr("value", val.id)
												.attr("selected", "selected")
												.text(val.result)
										);
									    $("input[name=make_id]").val(val.id);
										
									}else {
										$(".ajax-dropdowns select#" + "edit-manufacturer").append(
											  $("<option/>")
												.attr("value", val.id)
												.attr("selected", val.selected ? "selected" : null)
												.text(val.result)
										);
									}
									
									
								});
							});
							
					 }else {
						 //array is empty
						 $(".ajax-dropdowns select#" + "edit-manufacturer").empty();
						 $('#edit-manufacturer option:selected').removeAttr('selected');

						 $(".ajax-dropdowns select#" + "edit-manufacturer").append(
							  $("<option/>")
								.attr("value", '')
								.attr("selected", "selected" )
								.text('No Results Found')
						 );
						 $("input[name=make_id]").val('');
					 }
					
				 }else {
						$(".ajax-dropdowns select#" + "edit-manufacturer").empty();
						$('#edit-manufacturer option:selected').removeAttr('selected');

						$(".ajax-dropdowns select#" + "edit-manufacturer").append(
						  $("<option/>")
							.attr("value", '')
							.attr("selected", "selected" )
							.text('No Results Found')
						);
						
						$("input[name=make_id]").val('');
				 }
				
				
				
				
			  },
			  error:function(){
				//remove gif
				loading.hide();
			  },
			  complete: function(){
				//..your complete logic
				alert('edit_manufacturer_id=='+$("input[name=make_id]").val());
				var path = 'model/'+$("input[name=make_id]").val();
				//getModelFromMake(path,$("input[name=make_id]").val());
				if($("input[name=make_id]").val() != ''){
					setTimeout(function () {
						getModelFromMake(path,$("input[name=make_id]").val());
					}, 10000);
				}
				
			  }
			});
	 }

		var loading = $("#loading");
		// when an ajax request is made, show the laoding gif
		$(document).ajaxStart(function () {
			loading.show();
		});
		// when the ajax call stops, hide the loading gif
		$(document).ajaxStop(function () {
			loading.hide();
			
		});
		
		
		function getModelFromMake(path,make_id){
			
			loading.show();
			jQuery.ajax({
			  url: Drupal.url(path),
			  type: "POST",			  
			  dataType: "json",
			  data: {
				make_id: make_id
			  },
			  cache: false,
			  beforeSend: function(x) {
				if (x && x.overrideMimeType) {
				  x.overrideMimeType("application/json;charset=UTF-8");
				}
			  },
			  success: function(result) {
				 loading.hide();
				 $(".ajax-dropdowns select#" + "edit-model").empty();
				 $(".ajax-dropdowns select#" + "edit-model").removeAttr('disabled');
				   
				   if(result.status == 200){
					   if (typeof result.model != "undefined" && result.model != null && result.model.length != null && result.model.length > 0) {
					   
							$.each(result.model, function (index, value) {						
								$.each(value, function (i, val) {

									if(index == 0){
											$(".ajax-dropdowns select#" + "edit-model").append(
												  $("<option/>")
													.attr("value", val.id)
													.attr("selected", "selected")
													.text(val.result)
											);
											$("input[name=model_id]").val(val.id);
											
									}else {	
										$(".ajax-dropdowns select#" + "edit-model").append(
											  $("<option/>")
												.attr("value", val.id)
												.attr("selected", val.selected ? "selected" : null)
												.text(val.result)
										);
									}
								});
							});
					   
					   }
					   else{
						   
						$(".ajax-dropdowns select#" + "edit-model").empty();
						$('#edit-model option:selected').removeAttr('selected');

						$(".ajax-dropdowns select#" + "edit-model").append(
						  $("<option/>")
							.attr("value", '')
							.attr("selected", "selected" )
							.text('No Results Found')
						);
						
						$("input[name=model_id]").val('');
						   
					   }
					   
				   }else {
					   
					    $(".ajax-dropdowns select#" + "edit-model").empty();
						$('#edit-model option:selected').removeAttr('selected');

						$(".ajax-dropdowns select#" + "edit-model").append(
						  $("<option/>")
							.attr("value", '')
							.attr("selected", "selected" )
							.text('No Results Found')
						);
						
						$("input[name=model_id]").val('');
					   
				   }
				
					
				
				
			 },
			 error:function(){
				//remove gif
				loading.hide();
			 },
			  complete: function(){
				//..your complete logic
				alert('edit_model_id=='+$("input[name=model_id]").val());
				var path = 'type/'+$("input[name=model_id]").val();
				//getTypeFromModel(path,$("input[name=model_id]").val());
				if($("input[name=model_id]").val() !=''){
					setTimeout(function () {
						getTypeFromModel(path,$("input[name=model_id]").val());
					}, 10000);
				}
				
			  }

			});
			
		}
		
	    

	  $(".ajax-dropdowns select#" + "edit-manufacturer").on('change', function() {
         var make_id = this.value;
		 $('#edit-manufacturer option').removeAttr('selected');		
		 $("#edit-manufacturer option[value='"+make_id+"']").attr('selected', true);		 
		 alert('make_id=='+make_id);
		 $("input[name=make_id]").val(make_id);
		 if(make_id !=''){			
			var path = 'model/'+make_id;
			getModelFromMake(path,make_id); 
		 }
		 
    });
	
	
	function getTypeFromModel(path,model_id){
		
		loading.show();
		jQuery.ajax({
			  url: Drupal.url(path),
			  type: "POST",			  
			  dataType: "json",
			  data: {
                model_id: model_id
              },
			  cache: false,
			  beforeSend: function(x) {
				if (x && x.overrideMimeType) {
				  x.overrideMimeType("application/json;charset=UTF-8");
				}
			  },
			  success: function(result) {
				 loading.hide();
				 $(".ajax-dropdowns select#" + "edit-type").removeAttr('disabled');
				 $(".ajax-dropdowns select#" + "edit-type").empty();
				 
				  if(result.status == 200){
					   if (typeof result.type != "undefined" && result.type != null && result.type.length != null && result.type.length > 0) {
						  $.each(result.type, function (index, value) {					
							$.each(value, function (i, val) {
							
								if(index == 0){
										$(".ajax-dropdowns select#" + "edit-type").append(
											  $("<option/>")
												.attr("value", val.id)
												.attr("selected", "selected")
												.text(val.result)
										);
										$("input[name=type_id]").val(val.id);
										
								}else {									
									$(".ajax-dropdowns select#" + "edit-type").append(
										  $("<option/>")
											.attr("value", val.id)
											.attr("selected", val.selected ? "selected" : null)
											.text(val.result)
									);
								}
							});
						}); 
					   }else{
							$(".ajax-dropdowns select#" + "edit-type").empty();
							$('#edit-type option:selected').removeAttr('selected');

							$(".ajax-dropdowns select#" + "edit-type").append(
							  $("<option/>")
								.attr("value", '')
								.attr("selected", "selected" )
								.text('No Results Found')
							);
							
							$("input[name=type_id]").val('');
						}
				  }else{
					    $(".ajax-dropdowns select#" + "edit-type").empty();
						$('#edit-type option:selected').removeAttr('selected');

						$(".ajax-dropdowns select#" + "edit-type").append(
						  $("<option/>")
							.attr("value", '')
							.attr("selected", "selected" )
							.text('No Results Found')
						);
						
						$("input[name=type_id]").val('');
				  }
				 
				
				
             },
			 error:function(){
				//remove gif
				loading.hide();
			 },
			complete: function(){
				//..your complete logic
				alert('edit_type_id=='+$("input[name=type_id]").val());
				
				
				var path = 'recommendation?typeid='+$("input[name=type_id]").val();
				//loadRecommendationPageFromType(path,$("input[name=type_id]").val());
				if($("input[name=type_id]").val() !=''){
					setTimeout(function () {
						//loadRecommendationPageFromType(path,$("input[name=type_id]").val());				
					}, 20000);
				}
				
				
				 
			}
      
        });
		
	}
	
	
	function loadRecommendationPageFromType(path,type_id){
		window.location.href =	"http://localhost:8080/gulf_dev/en/"+path;return false; 
	}
	$(".ajax-dropdowns select#" + "edit-model").on('change', function() {
         var model_id = this.value; 
		 //alert('model_id=='+model_id);
		 $("input[name=model_id]").val(model_id);
		 if(model_id !=''){
			
			var path = 'type/'+model_id;
			getTypeFromModel(path,model_id);
		 }
		 		 
    });
	  
	$(".ajax-dropdowns select#" + "edit-type").on('change', function() {
         var type_id = this.value; 
		 //alert('type_id=='+type_id);
		 $("input[name=type_id]").val(type_id);
		 
		 if(type_id !=''){			
			var path = 'recommendation?typeid='+type_id;	
			//loadRecommendationPageFromType(path,type_id); 	
		 }
		 	 
	});
	
	
	
	$("#sub_filters_search a").click(function() {
        //alert('sub_filters_search a clicked');
		
		/*var type = $("input[name=type_id]").val();
		var model = $("input[name=model_id]").val();
		var make = $("input[name=make_id]").val();
		var category = $("input[name=category_id]").val();*/
		
	    var category ='1';
		var make = '10be247fe539bb03';
		var model = '0768b6ef9f584bb7';
		var type ='485c77f997875de22917bc900420aa0e';
		
		//alert(category+'' +make +'' +model+'' +type);
		
		if( (category.length != 0) && (make.length != 0) && (model.length != 0) && (type.length != 0) ){
			//alert('recommendation');
			var path = 'recommendation?typeid='+type;	
			loadRecommendationPageFromType(path,type); 	
		}
		
    });
	
	
	 $("body").on("click", ".minimize", function () { //alert('minize click');
        $(this)
          .closest(".recommed-list")
          .find(".fa")
          .toggleClass("fa-minus fa-plus");
        $(this)
          .closest(".recommed-list")
          .find(".recommed-list-content")
          .slideToggle();
      });
	
	/*$(".submit_overall_search").click(function () { alert('submit_overall_search click'); return false;
		window.location.href = "/search_lubricant_advisor?q=" + $("input[name=q]").val();
		//return false;
		
	  
		 var search_text = $("input[name=q]").val(); 
		 alert('search_text=='+search_text);
		
		 var path = 'search_lubricant_advisor/'+search_text;
		 loading.show();
		 jQuery.ajax({
			  url: Drupal.url(path),
			  type: "POST",			  
			  dataType: "json",
			  data: {
                search_text: search_text
              },
			  cache: false,
			  beforeSend: function(x) {
				if (x && x.overrideMimeType) {
				  x.overrideMimeType("application/json;charset=UTF-8");
				}
			  },
			  success: function(result) {
				 loading.hide();
				
				 console.log('result'+result.type); 
                
				$.each(result.type, function (index, value) {
					
					$.each(value, function (i, val) {
						
						console.log('id===='+val.id); console.log('result===='+val.result);
						console.log('count==='+count);
						
						//searchResult
						
						$(".ajax-dropdowns select#" + "edit-type").append(
							  $("<option/>")
								.attr("value", val.id)
								.attr("selected", val.selected ? "selected" : null)
								.text(val.result)
						);
					});
				});
				
             },
			 error:function(){
				//remove gif
				loading.hide();
			 }
      
        });
	  
	  
	  
	  
		  var columnArray = [{
			  title: "Photo ID",
			  data: "id",
			  targets: 0
			},
			{
			  title: "Photo Title",
			  data: "title",
			  targets: 1
			}
		  ];

		  $('#dataList').DataTable({
			ajax: {
			  url: 'https://jsonplaceholder.typicode.com/photos',
			  type: 'GET',
			  dataSrc: ''
			},
			bBootstrapLayout: true,
			columns: columnArray,
			columnDefs: [{
			  render: function(data, type, row) {
				return data + ' (' + row['albumId'] + ')';
			  },
			  targets: 0
			}, {
			  render: function(data, type, row) {
				var html = '<button>view</button>';

				return html;
			  },
			  targets: 2
			}]
		  });

	  
    });
  });
  */
/* Olyslager API integration js code */



    /*function renderDropdowns(url) {
      console.log(url);
      // for the loading gif
      var loading = $("#loading");
      // when an ajax request is made, show the laoding gif
      $(document).ajaxStart(function () {
        loading.show();
      });
      // when the ajax call stops, hide the loading gif
      $(document).ajaxStop(function () {
        loading.hide();
      });
      //url = 'http://guloilmaster.hgsinteractive.in:9090/en' + url;
      // set the browse path
      var ajaxdata = $.getJSON(url, function (json) {
        // clear the dropdowns with each call. this means if a new selection is made
        // when a selecion is already in progress all later steps clear automatically
				//$(".ajax-dropdowns form").empty();
        // set the stepcounter
        var stepcounter = 1;
        // loop through each browse step
        $.each(json.browse.browse, function (i, j) {
          // years are returned chronologically, reverse them so the newest year is at the top
          if (i == "year") {
            j.reverse();
          }
          // add a dropdown to the end of the dropdowns form
          if (i != "familygroup") {
            $(".ajax-dropdowns form").append(
              "<div class='dropDownSelectors " +
                i +
                " searchStep" +
                stepcounter +
                " col-md-6'><label for='" +
                i +
                "'>" +
                browseLabels[i] +
                "</label><select id='" +
                i +
                "' name='" +
                i +
                "'><option value='noSelection'>- Please Select</option></select></div>"
            );
          }
          // loop through the options for the dropdown
          $.each(j, function (index, val) {
            // add the options to the new dropdown
            $(".ajax-dropdowns select#" + i).append(
              $("<option/>")
                .attr("value", val.href)
                .attr("selected", val.selected ? "selected" : null)
                .text(val.name)
            );
          });
          $(".ajax-dropdowns select#" + i).append(
            "<optgroup label=''></optgroup>"
          );
          // add one to the stepcounter
          stepcounter = stepcounter + 1;
          // addhandlers updates the browse url when you choose something from the dropdown, because the dropdowns dont exist on page load
          // the handler needs to be called when the script first runs on page load
          addHandlers();
        });
        ajaxDropdownsUpdateSectorIcons();
        // render the disabled drodpowns that are defined by the always show option
        // only render them if the browse path isn't displaying equipment, so any steps that are
        // not required disappear
        if (!json.browse.equipment) {
          // loop through the list of steps returned by the getAlwaysShow function
          // this is the steps defined by the always show option, minus any steps already shown by the API
          $.each(getAlwaysShow(json.browse), function (index, val) {
            // create a disabled dropdown at the end of the active dropdowns shown by the API
            if (val != "familygroup") {
              $(".ajax-dropdowns form").append(
                "<div class='dropDownSelectors " +
                  val +
                  " searchStep" +
                  stepcounter +
                  " col-md-6'><label for='" +
                  val +
                  "'>" +
                  browseLabels[val] +
                  "</label><select id='" +
                  val +
                  "' name='" +
                  val +
                  "' disabled><option>- Please Select</option></select></div>"
              );
            }
            // add one to the stepcounter
            stepcounter = stepcounter + 1;
          });
        }
        // only do this when the API returns equimpent selections
        if (json.browse.equipment) {
          // add a final dropdown to show the equipment
          $(".ajax-dropdowns form").append(
            "<div class='dropDownSelectors equipment searchStep" +
              stepcounter +
              " col-md-6'><label for='equipment'>" +
              browseLabels["equipment"] +
              "</label><select id='equipment' name='equipment'><option>- Please Select</option></select></div>"
          );
          // loop through the available equipment and add all of the options
          $.each(json.browse.equipment, function (index, val) {
            $("select#equipment").append(
              $("<option/>")
                .attr("value", val["@href"])
                .text(val.display_name_long)
            );
          });
          $("select#equipment").append("<optgroup label=''></optgroup>");
          $("select#equipment").change(function () {
            $("#equipment_button")
              .prop("disabled", false)
              .removeClass("disabled");
          });
          // either load the equipment path when equipment is selected (default)
          // or user has to click show equipment button
          equipmentPathMethod();
        } else {
          // if no equipment is available to select, display a disabled dropdown for equipment
          // always shows and is always last
          //$("#equipment_button").prop("disabled", true).removeClass("disabled").addClass("disabled");
          //$(".ajax-dropdowns form").append("<div class='dropDownSelectors equipment searchStep" + stepcounter + "'><label for='equipment'>" + browseLabels['equipment'] + "</label><select id='equipment' name='equipment' disabled><option>- Please Select</option></select></div>");
        }
      });
    }*/
    // set the base browse path on first page load
    
    //renderDropdowns("/search");
    ajaxIcons();
    facetSearchDropdown();
    updateSectorIcons();
    if ($("#lubricantfinder-form2 input").val() == "") {
      //Default
      setTimeout(function () {
        //$('a[data-path="category/1"]').trigger("click");
		$('.sectorIcon .sectorIconImage').find('a[data-path="category/1"]').trigger("click");
		//renderMakeDropDown("category/1","1");
      }, 2000);
    }



	
    // when you make a selection in a dropdown, update the browse url with the value of the dropdown
    function addHandlers() {
      $(".ajax-dropdowns select").change(function () {
        var path = $(this).val();
        // check if "please select" got selected after an option was chosen
        if (path === "noSelection") {
          // if it was, get the browse path from the previous selected dropdown
          path = $(this)
            .parent(".dropDownSelectors")
            .prev()
            .children("select")
            .children("option:selected")
            .val();
        }
        // set the path to either be the new selection, or the previous selection if "please select" was chosen
        var url = path;
        // re-render the dropdowns when a selection is made
			//renderDropdowns(url);
      });
    }

    function ajaxIcons() {
      $(".sectorIcon").click(function (event) {
        event.preventDefault();
        $(".active").removeClass("active");

        $(this).addClass("active").find(".icon").first().addClass("active");
        var path = $(this)
          .children(".sectorIconImage")
          .children("a")
          .attr("href");
				//renderDropdowns(path);

        var thisSector = $(this).find("a").first().data("path");
      });
    }

    function facetSearchDropdown() {
      // this will update the facet search dropdown when a sector icon is clicked
      // check if facet search is used
      if ($(".facetDropdown").length) {
        // when you click on a sector icon
        $(".sectorIcon").click(function () {
          // get the clicked sector icon
          var selectedsector = $(this).find(".sectorIconText").first().text();
          // set the facet search box to match the clicked sector
          $(".modelSearchContainer select#familygroup").val(selectedsector);
        });
      }

      // this will update the facet search radio buttons if that version of facet search is used
      // check if the facet search with radio buttons is used
      if ($(".facetRadio #modelSearchSelect").length) {
        // when the sector icon is clicked
        $(".sectorIcon").click(function () {
          // get the clicked sector icon
          var selectedsector = $(this).find(".sectorIconText").first().html();
          // set the facet radio button to match the clicked sector
          // this needed to use a filter as special characters would break the prop selection
          $("#modelSearchSelect input")
            .filter(function () {
              return this.value === selectedsector;
            })
            .prop("checked", true);
        });
      }
    }

    function updateSectorIcons() {
      // this will update the selected sector icons and the selected sector in the
      // dropdown search if a sector is chosen in the facet search dropdown
      // check if facet search is used
      if ($(".facetDropdown").length) {
        //check if the sector icons are used
        if ($("#iconBox").length) {
          // when you change the facet search dropdown
          $("select#familygroup").change(function () {
            // take the value of the facet search dropdown
            var value = $(this).val();
            if (value == "") {
              location.reload();
            }
            // find the sector icon label that matches the dropdown, and click the icon
            // this deals with the selected icon image, classes etc, and updates the path
            // in the ajax dropdowns
            $(".sectorIconText:contains(" + value + ")")
              .parent(".sectorIcon")
              .click();
          });
          // if there are no sector icons, see if there are ajax dropdowns instead
        } else if ($(".ajax-dropdowns").length) {
          // when you change the facet search dropdown
          $(".modelSearchContainer select#familygroup").change(function () {
            // take its value
            var value = $(this).val();
            // find the ajax dropdown familygroup that has the same value and set it to selected
            $(
              ".ajax-dropdowns select#familygroup option:contains(" +
                value +
                ")"
            ).attr("selected", "selected");
            // get the path of the selected familygroup
            var path = $(".ajax-dropdowns select")
              .attr("selected", "selected")
              .val();
            // append it to the full path to include the language
            var url = path;
            // pass it to the render dropdowns handler to update the ajax path
				//renderDropdowns(url);
          });
        }
      }
      // this will update the selected sector icons and the selected sector in the
      // dropdown search if a sector is chosen in the facet search radio buttons
      // check that the facet search radio component is used
      if ($(".facetRadio #modelSearchSelect").length) {
        // check that sector icons are enabled
        if ($("#iconBox").length) {
          // when a facet search radio button is clicked
          $(".facetRadio #modelSearchSelect input").click(function () {
            // get its value
            var value = $(this).val();
            // find the sector icon label that matches the selected radio button, and click the icon
            // this deals with the selected icon image, classes etc, and updates the path
            // in the ajax dropdowns
            $(".sectorIconText:contains(" + value + ")")
              .parent(".sectorIcon")
              .click();
          });
          // if there are no sector icons just update the ajax dropdown path
        } else {
          // when a facet search radio button is clicked
          $(".facetRadio #modelSearchSelect input").click(function () {
            // take its value
            var value = $(this).val();
            // find the ajax dropdown familygroup that has the same value and set it to selected
            $(
              ".ajax-dropdowns select#familygroup option:contains(" +
                value +
                ")"
            ).attr("selected", "selected");
            // get the path of the selected familygroup
            var path = $(".ajax-dropdowns select")
              .attr("selected", "selected")
              .val();
            // append it to the full path to include the language
            var url = "/" + path;
            // pass it to the render dropdowns handler to update the ajax path
				//renderDropdowns(url);
          });
        }
      }
    }

    function ajaxDropdownsUpdateSectorIcons() {
      // this will update the selected sector icons and the selected sector in the
      // facet search dropdown if a sector is chosen in the ajax search dropdown
      // check that sector icons are enabled
      if ($("#iconBox").length) {
        // when a new familygroup is chosen in the ajax dropdowns
        $(".ajax-dropdowns select#familygroup").change(function () {
          // get the text value of the selected familygroup
          var value = $(
            ".ajax-dropdowns select#familygroup option:selected"
          ).text();
          // find the sector icon label that matches the dropdown, and click the icon
          // this deals with the selected icon image, classes etc, and updates the path
          // in the ajax dropdowns
          $(".sectorIconText:contains(" + value + ")")
            .parent(".sectorIcon")
            .click();
        });
        // if there are no sector icons, see if the facet search is being used and update that
      } else if ($(".facetDropdown").length) {
        // when a new familygroup is chosen is the ajax dropdowns
        $(".ajax-dropdowns select#familygroup").change(function () {
          // get the text value of the selected familygroup
          var value = $(
            ".ajax-dropdowns select#familygroup option:selected"
          ).text();
          // find the facet search option that matches and select it
          $(".modelSearchContainer select#familygroup").val(value);
        });
        // if there are no sector icons, see if the facet search radio button
        // component is being used and update that
      } else if ($(".facetRadio #modelSearchSelect").length) {
        // when a new familygroup is chosen is the ajax dropdowns
        $(".ajax-dropdowns select#familygroup").change(function () {
          // get the text value of the selected familygroup
          var selectedsector = $(
            ".ajax-dropdowns select#familygroup option:selected"
          ).text();
          // find the facet search option that matches and select it
          $("#modelSearchSelect input")
            .filter(function () {
              return this.value === selectedsector;
            })
            .prop("checked", true);
        });
      }
    }
    $("#printid").on("click", function () {
      // var divContents = document.getElementById("Content").innerHTML;
      // var headcontent = document.getElementsByTagName("head")[0].innerHTML;
      // var printWindow = window.open('', '', '');
      // printWindow.document.write('<html><head><title>Recommendations</title>');
      // printWindow.document.write(headcontent+'</head><body >');
      // printWindow.document.write(divContents);
      // printWindow.document.write('</body></html>');
      // printWindow.document.close();
      // printWindow.print();
      setTimeout(function () {
        window.print();
        $("body");
      }, 100);
    });
    $("#mail").on("click", function () {
      return false;
    });
    $(window).on("load", function () {
      $(".toggle_content").append(
        '<label class="equipment_display">Normal View </label><label class="switch"><input type="checkbox" checked><span class="slider slider1 round"></span></label><label class="active equipment_display"> Detailed View</label>'
      );
    });
    $(document).on("click", ".slider1", function (event) {
      //event.preventDefault();
      $(".toggle_content")
        .find("label.equipment_display")
        .toggleClass("active");
      $(".lubricant_view").toggleClass("active");
    });

    $(document).on("click", ".expand", function (event) {
      $(this).toggleClass("active");
      var eqiupment_list = $(this).data("eqcontent");
      if ($(this).hasClass("active")) {
        $(this).attr("src", "/modules/custom/lubricantfinder/images/minus.png");
      } else {
        $(this).attr("src", "/modules/custom/lubricantfinder/images/plus.png");
      }
      $("." + eqiupment_list).toggleClass("active");
    });

    //var ajax_url = 'http://guloilmaster.hgsinteractive.in:9090/en';
    $(".facet_dropdown").change(function () {
      window.location.href = $(this).find("option:selected").val();
    });
    /*$(".submit_overall_search").click(function () {
      window.location.href = "/search_lubricant?q=" + $("input[name=q]").val();
      return false;
    });*/
    if ($("input[name=q]").val() != "") {
      $("#modelSearchButton > input").attr("disabled", true);
      $("#modelSearchSelect > select").attr("disabled", true);
    }
    $("input[name=q]").on("keyup", function () {
      $("#modelSearchButton > input").removeAttr("disabled");
    });
    $("form.lubricantfinder-form").addClass("row");
  });
});

var DreamSparkSSO = {
	User: {
		accountName: "",
		displayName: "",
		imgSrc: ""
	},
	AppLauncher: {
		Load: function(element) {
			element = $(element)[0];
			var appChrome = new Office.Controls.AppChrome("DreamSpark SSO", element, {
				login: function() {
					console.log("login");
				},
				logout: function() {
					window.location = "/logout";
				},
				getUserInfoAsync: function(callback) {
					callback(null, DreamSparkSSO.User);
				},
				/*hasLogin: function() {
					console.log("hasLogin");
					return true;
				}*/
			}, {
				"appHomeUrl": "https://dreamspark.edulog.in",
				"onSignIn": function() {
					console.log("onSignIn");
				},
				"onSignOut": function() {
					console.log("onSignOut");
				},
				"customizedItems": {
					"Office 365": "http://portal.office.com",
					//"EduLog.in": "http://edulog.in",
				}
			});
			return appChrome;
		}
	},
	Settings: {
		Permissions: {
			init: function() {
				//prevent same groups being selected
				$(".group-selection").select2({
					multiple: true,
					ajax: {
						url: "/ajax/groups",
						dataType: 'json',
						delay: 500,
						data: function(params) {
							return {
								q: params.term
							}
						},
						processResults: function(data, page) {
							$.each(data, function(index, item) {
								item.id = item.objectId;
							});
							return {
								results: data
							};
						},
						cache: false
					},
					escapeMarkup: function(markup) { return markup; },
					//minimumInputLength: 1,
					templateResult: function(object) {
						if(object.loading) return object.text;
						
						var markup = '<div class="clearfix">' +
							'<div>' + object.displayName + '</div>' +
							(object.description ? '<div class="ms-font-s">' + object.description + '</div>' : '') +
							'</div>';
	
						return markup;
					},
					templateSelection: function(object) {
                    	return object.displayName || object.text;
					}
				});
				$("[name=access]").change(function() {
					var selected = $(this).val();
					if(selected == "everyone") DreamSparkSSO.Settings.Permissions.selectEveryone();
					else if(selected == "groups") DreamSparkSSO.Settings.Permissions.selectGroups();
				});
				$("[name=access]:checked").trigger('change');
				$("[name=students\\[\\]]").on('select2:select', function(e) {
					var faculty = $("[name=faculty\\[\\]]").select2('val');
					var staff = $("[name=staff\\[\\]]").select2('val');
					
					var merged = faculty.concat(staff);
					var found = merged.indexOf(e.params.data.id);
					if(e.params.data != undefined && found > -1) {
						alert("You cannot assign the same group to multiple roles!");
						var newValue = [];
						$.each($(this).select2('val'), function(index, value) {
							if(value != e.params.data.id) {
								newValue.push(value);
							}
						});
						$(this).val(newValue).trigger('change');
					}
				});
				$("[name=faculty\\[\\]]").on('select2:select', function(e) {
					var students = $("[name=students\\[\\]]").select2('val');
					var staff = $("[name=staff\\[\\]]").select2('val');
					
					var merged = students.concat(staff);
					var found = merged.indexOf(e.params.data.id);
					if(e.params.data != undefined && found > -1) {
						alert("You cannot assign the same group to multiple roles!");
						var newValue = [];
						$.each($(this).select2('val'), function(index, value) {
							if(value != e.params.data.id) {
								newValue.push(value);
							}
						});
						$(this).val(newValue).trigger('change');
					}
				});
				$("[name=staff\\[\\]]").on('select2:select', function(e) {
					var students = $("[name=students\\[\\]]").select2('val');
					var faculty = $("[name=faculty\\[\\]]").select2('val');
					
					var merged = students.concat(faculty);
					var found = merged.indexOf(e.params.data.id);
					if(e.params.data != undefined && found > -1) {
						alert("You cannot assign the same group to multiple roles!");
						var newValue = [];
						$.each($(this).select2('val'), function(index, value) {
							if(value != e.params.data.id) {
								newValue.push(value);
							}
						});
						$(this).val(newValue).trigger('change');
					}
				});
			},
			selectEveryone: function() {
				$("#groups").hide();
			},
			selectGroups: function() {
				$("#groups").show();
			},
		}
	}
};
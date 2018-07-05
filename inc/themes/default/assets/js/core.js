$(function() {
    NPTheme.General();
    NPTheme.LangSelector();
});


/**
 * NPTheme (Next Post Theme) Namespace
 */
var NPTheme = {};

    /**
     * General
     */
    NPTheme.General = function()
    {
        $("body").on("input focus", ":input", function() {
            $(this).removeClass("error");
        });

        $("body").on("input", ".fancy-field .input", function() {
            if ($(this).val().length > 0) {
                $(this).addClass("hasvalue");
            } else {
                $(this).removeClass("hasvalue");
            }
        });

        $(".fancy-field .input").each(function(index, el) {
            $(this).trigger("input")
        });
    }


    /**
     * Langugae selector
     */
    NPTheme.LangSelector = function()
    {
        $(".footer-lang-selector").on("change", function() {
            var lang = $(this).val();
            window.location.href = $(this).data("base")+"/"+lang;
        })
    }


    /**
     * Signup
     */
    NPTheme.Signup = function()
    {
        var $form = $("#signup form");

        $form.on("submit", function() {
            var submitable = true;

            $form.find(".js-required").each(function() {
                if (!$(this).val()) {
                    $(this).addClass("error");
                    submitable = false;
                }
            });

            if (!isValidEmail($form.find(":input[name='email']").val())) {
                $form.find(":input[name='email']").addClass("error");
                submitable = false;
            }

            if (!submitable) {
                return false;
            }
        })
    }



    /**
     * Login with facebook
     * @param {string} appid Facebook App ID
     */
    NPTheme.FacebookLogin = function(appid, url) 
    {   
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));


        window.fbAsyncInit = function() {
            FB.init({
                appId      : appid,
                cookie     : true,  
                xfbml      : true,  
                version    : 'v2.9'
            });

            $(".fbloginbtn").fadeIn(500);

            $("body").on("click", ".fbloginbtn", function(){
                FB.login(function(auth){
                    if(auth.status == "connected"){
                        FB.api('/me', {fields: 'first_name, last_name, email'}, function(userinfo) {
                            $("body").addClass("onprogress");

                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'jsonp',
                                data: {
                                    action: "fblogin",
                                    firstname: userinfo.first_name,
                                    lastname: userinfo.last_name,
                                    email: userinfo.email,
                                    token: auth.authResponse.accessToken,
                                    userid: auth.authResponse.userID
                                },

                                error: function(){
                                    $("body").removeClass("onprogress");
                                },

                                success: function(resp){
                                    if(resp.result == 1) {
                                        window.location.href = resp.redirect;
                                    } else {
                                        $("body").removeClass("onprogress");
                                    }
                                }
                            })
                        });
                    }
                }, { scope: 'email, public_profile' })
            })
        };
    }

    
/* Functions */

/**
 * Validate Email
 */
function isValidEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}
$('head').append('<link rel="stylesheet" href="https://local.4libs.org/apps/summon/ShowJournal.css" type="text/css" />');
$('div#rightPane div.customSections li h3[ng-bind="::section.title"]').filter(function(index){ return $(this).text()=="Journal & Book"}).parent(".content").css("display","none");
setTimeout(function() {
  callAtoZ();
}, 500);



function callAtoZ() {
  var currentURL=location.href;
  var currentQuery=getQueryParameter( currentURL,"q");
  var scripts = document.getElementsByTagName('script');
  var myScript;
  for( var i=0; i<scripts.length; i++) {
    if(scripts[i].src.search("ShowJournal.js")>0) {
      myScript=scripts[i].src;
      break;
    }
  }
  var queryString = myScript.replace(/^[^\?]+\??/,'');
  var params = parseQuery( queryString );
  var libhash=params['libhash'];
  var onlyJournal=false;
  var onlyBook=false;
  if( "journal" in params ) {
    if(params['journal'])
      onlyJournal=true;
  }
  if( "book" in params ) {
    if(params['book'])
      onlyBook=true;
  }
  var yql="https://local.4libs.org/apps/summon/GetJournalAndBook.php?libhash="+libhash+"&title="+currentQuery;

  $.ajax({
    type: 'GET',
    dataType: 'jsonp',
    crossDomain: true,
    url:yql,
    success: function(data) {
      var number = Object.keys(data).length;
      var contents='';
      var journalContents="";
      var bookContents="";
      var lineFeed="<br>";


      if(number>0) {
        $('div#rightPane div.customSections li').first().css("display","block");
        //$('div#rightPane div.customSections li h3[ng-bind="::section.title"]').first().css("display","none");
      }

      for( var i=0; i<number; i++) {
        var title=data[i]['title'];
        var pidentifer=data[i]['pidentifer'];
        var eidentifer=data[i]['eidentifer'];
        var format=data[i]['format'];
        var dbLine;
        var space="&nbsp";
        var comma=",";
        var colon=":";

        content=content+"<div>";
        if((format=='journal')&&(onlyBook==false)) {
          if(title) {
            journalContents=journalContents+"<span><b>"+title+"</b></span>";
          }
          if(pidentifer) {
            journalContents=journalContents+"<span>"+space+pidentifer+"</span>";
          }
          if(eidentifer) {
            if(pidentifer)
              journalContents=journalContents+"<span>"+comma+space+eidentifer+"</span>";
            else
              journalContents=journalContents+"<span>"+space+eidentifer+"</span>";
          }

          var holdingCount=data[i]['holdings']['dbname'].length;
          for( var j=0; j<holdingCount; j++) {
            if((data[i]['holdings']['dbname'][j])&&(data[i]['holdings']['url'][j])) {
              dbLine="<div style='text-indent:15px'><a target='_blank' href='"+data[i]['holdings']['url'][j]+"'>"+data[i]['holdings']['dbname'][j]+"</a>";
            }
            else if(data[i]['holdings']['dbname'][j]) {
              dbLine="<div style='text-indent:15px'>"+data[i]['holdings']['dbname'][j];
            }

            if(data[i]['holdings']['startdate'][j]) {
              dbLine=dbLine+"&nbsp;"+"from"+"&nbsp;"+data[i]['holdings']['startdate'][j];
            }

            if(data[i]['holdings']['enddate'][j]) {
              dbLine=dbLine+"&nbsp;"+"to"+"&nbsp;"+data[i]['holdings']['enddate'][j];
            }
            else {
              dbLine=dbLine+"&nbsp;"+"to present";
            }
            dbLine=dbLine+"</div>";
            journalContents=journalContents+dbLine;
            dbLine="";
          }
        }
        else if((format=='book')&&(onlyJournal==false)) {
          if(title) {
            bookContents=bookContents+"<span><b>"+title+"</b></span>";
          }
          if(pidentifer) {
            bookContents=bookContents+"<span>"+space+pidentifer+"</span>";
          }
          if(eidentifer) {
            if(pidentifer)
              bookContents=bookContents+"<span>"+comma+space+eidentifer+"</span>";
            else
              bookContents=bookContents+"<span>"+space+eidentifer+"</span>";
          }

          var holdingCount=data[i]['holdings']['dbname'].length;
          for( var j=0; j<holdingCount; j++) {
            if((data[i]['holdings']['dbname'][j])&&(data[i]['holdings']['url'][j])) {
              dbLine="<div style='text-indent:15px'><a target='_blank' href='"+data[i]['holdings']['url'][j]+"'>"+data[i]['holdings']['dbname'][j]+"</a>";
            }
            else if(data[i]['holdings']['dbname'][j]) {
              dbLine="<div style='text-indent:15px'>"+data[i]['holdings']['dbname'][j];
            }

            if(data[i]['holdings']['startdate'][j]) {
              dbLine=dbLine+"&nbsp;"+"from"+"&nbsp;"+data[i]['holdings']['startdate'][j];
            }

            if(data[i]['holdings']['enddate'][j]) {
              dbLine=dbLine+"&nbsp;"+"to"+"&nbsp;"+data[i]['holdings']['enddate'][j];
            }
            else {
              dbLine=dbLine+"&nbsp;"+"to present";
            }
            dbLine=dbLine+"</div>";
            bookContents=bookContents+dbLine;
            dbLine="";
          }
        }
      }
      if(journalContents)
        contents="<a href='#' class='format'>Journal</a>"+lineFeed+journalContents+lineFeed;

      if(bookContents)
        contents=contents+"<a href='#' class='format'>Book</a>"+lineFeed+bookContents;

      contents=contents+"</div>";
      $('div#mydiv').html(contents);
      $('.format').css({
        "background-color":" #29b6f6 ",
        "-moz-border-radius":"3px",
        "-webkit-border-radius":"3px",
        "border-radius":"3px",
        "border":"0.3px solid  #e0e0e0 ",
        "display":"inline-block",
        "cursor":"pointer",
        "color":"#ffffff",
        "font-family":"Arial",
        "font-size":"13px",
        "font-weight":"bold",
        "padding":"5px 11px",
        "width":"100%",
        "text-decoration":"none"
      });
    }
  });
}

function parseQuery ( query ) {
  var Params = new Object ();
  if ( ! query ) return Params; // return empty object
  var Pairs = query.split(/[;&]/);
  for ( var i = 0; i < Pairs.length; i++ ) {
    var KeyVal = Pairs[i].split('=');
    if ( ! KeyVal || KeyVal.length != 2 ) continue;
    var key = unescape( KeyVal[0] );
    var val = unescape( KeyVal[1] );
    val = val.replace(/\+/g, ' ');
    Params[key] = val;
  }
  return Params;
}

function getQueryParameter ( source, parameterName ) {
  var queryString = source;
  var parameterName = parameterName + "=";

  if ( queryString.length > 0 ) {
    begin = queryString.lastIndexOf ( parameterName );
    if ( begin != -1 ) {
      begin += parameterName.length;
      end = queryString.lastIndexOf ( "&" , begin );
      if ( end == -1 || begin>=end ) {
        end = queryString.length
      }
      return queryString.substring ( begin, end );
    }
  }
  return "null";
}

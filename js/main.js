function change_title(title = "One80") {
  $('title').text(title);
}

function scrollDown() {
  const windowHeight = $(window).height();
  $('html, body').animate({ scrollTop: windowHeight }, 550);
}

function openPortrait(elem) {
  console.log($(elem).attr('data-path'));
  const path = $(elem).attr('data-path');
  window.location = path;
}

function portraitFull_movie_fullPlay(elem) {
  $(elem).parent().parent().find('video').get(0).play()
  portraitFull_movie_actions_play($('.portraitFull_movie_actions .portraitFull_movie_actions_play'))
  $(elem).remove();
}

function portraitTeaser_cursor() {
  $('.portraitTeaser').each(() => {
    $(this).get(0).addEventListener('mousemove', portraitTeaser_cursor_move)
  })
}

const portraitTeaser_cursor_move = (e)=> {
  const portraitTeaser = $(e.srcElement).closest('.portraitTeaser');
  if (typeof portraitTeaser.get(0) !== 'object') return;

  const elementPos = portraitTeaser.offset();
  const mouseY = e.pageY - (elementPos.top || 0);
  const mouseX = e.pageX - (elementPos.left || 0);

  $(e.srcElement).closest('.portraitTeaser').find('.portraitTeaser_cursor').get(0).style.transform = `translate3d(calc(${mouseX}px - 50%), calc(${mouseY}px - 50%), 0)`; 
}

function portraitFull_movie_actions_play(elem) {
  const action = $(elem).attr('data-action');

  if (action === 'pause') {
    $(elem).parent().parent().find('video').get(0).pause()
    $(elem).attr('data-action', 'play');
    $(elem).attr('title', 'play');
    $(elem).empty();
    $('<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>').appendTo(elem)
  }
  
  if (action === 'play') {
    $(elem).parent().parent().find('.portraitFull_movie_play').remove();
    $(elem).parent().parent().find('video').get(0).play()
    $(elem).attr('data-action', 'pause');
    $(elem).attr('title', 'pause');
    $(elem).empty();
    $('<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pause"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>').appendTo(elem);
  }
}

function portraitFull_movie_actions_mute(elem) {
  const action = $(elem).attr('data-action');

  if (action === 'mute') {
    $(elem).parent().parent().find('video').get(0).muted = true;
    $(elem).attr('data-action', 'unmute');
    $(elem).attr('title', 'unmute');
    $(elem).empty();
    $('<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-volume-x"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>').appendTo(elem)
  }
  
  if (action === 'unmute') {
    $(elem).parent().parent().find('video').get(0).muted = false;
    $(elem).attr('data-action', 'mute');
    $(elem).attr('title', 'mute');
    $(elem).empty();
    $('<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-volume-2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>').appendTo(elem);
  }
}

function portraitFull_movie_actions_close() {
  window.location = '#/';
  $('header').show();
}

function contactFormSubmit(button, event) {
  event.preventDefault();
  
  $('form .form_field_error').text('');

  $.ajax({
    url: 'api/submit',
    type: 'post',
    dataType: 'json',
    data: $('form').serialize(),
    success: (response) => {
      if (response.data.sent == false) {
        for(var key in response.data.errors) {
          $('form *[name="'+key+'"]').parent().find('.form_field_error').text(response.data.errors[key])
        }
      }

      if (response.data.sent == true) {
        $('form').trigger("reset");
      }
    }
  });
}
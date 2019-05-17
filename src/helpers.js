export const get_calendar_event_id = function(game) {
  var media = game.game_media.media;

  var calendar_event_id = "";
  if (Array.isArray(media)) {
    for (var i = 0; i < media.length; i++) {
      if (media[i].calendar_event_id) {
        calendar_event_id = media[i].calendar_event_id;
        break;
      }
    }
  } else {
    calendar_event_id = media.calendar_event_id;
  }

  return calendar_event_id;
};

export const get_base_situation = function(runners_on_base_status) {
  var first = false;
  var second = false;
  var third = false;
  var ordinal = parseInt(runners_on_base_status) + 1;
  switch (runners_on_base_status) {
    case "1":
      first = true;
      break;
    case "2":
      second = true;
      break;
    case "3":
      third = true;
      ordinal = 5;
      break;
    case "4":
      first = true;
      second = true;
      ordinal = 4;
      break;
    case "5":
      first = true;
      third = true;
      break;
    case "6":
      second = true;
      third = true;
      break;
    case "7":
      first = true;
      second = true;
      third = true;
      break;
  }
  return { first, second, third, ordinal };
};

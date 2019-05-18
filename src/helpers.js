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

export const get_base_situation = function(offense) {
  var first = !!offense.first;
  var second = !!offense.second;
  var third = !!offense.third;
  var ordinal = (first ? 1 : 0) + (second ? 2 : 0) + (third ? 4 : 0) + 1;
  return { first, second, third, ordinal };
};

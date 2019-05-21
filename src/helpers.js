export const get_base_situation = function(offense) {
  var first = !!offense && !!offense.first;
  var second = !!offense && !!offense.second;
  var third = !!offense && !!offense.third;
  var ordinal = (first ? 1 : 0) + (second ? 2 : 0) + (third ? 4 : 0) + 1;
  return { first, second, third, ordinal };
};

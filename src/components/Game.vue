<template>
  <div
    class="GC-linescore"
    :id="calendar_event_id"
    :class="{ 'GC-game-highlight': highlighted }"
  >
    <table class="table">
      <thead>
        <tr>
          <th width="100">{{ half + " " + inning }}</th>
          <th width="20">R</th>
          <th width="20">H</th>
          <th width="20">E</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ away.name }}</td>
          <td>{{ away.runs }}</td>
          <td>{{ away.hits }}</td>
          <td>{{ away.errors }}</td>
        </tr>
        <tr>
          <td>{{ home.name }}</td>
          <td>{{ home.runs }}</td>
          <td>{{ home.hits }}</td>
          <td>{{ home.errors }}</td>
        </tr>
        <tr>
          <td>Outs: {{ outs }}</td>
          <td colspan="3" class="center">
            <img
              :src="'images/Bases_' + basesit + '.png'"
              width="34"
              height="11"
              align="absmiddle"
            />
          </td>
        </tr>
      </tbody>
    </table>
    <div class="GC-game-info">
      Leverage Index: {{ leverage_index.toFixed(2) }}<br />Pitching: {{ pitcher
      }}<br />At Bat: {{ atbat }}<br />
      On Deck: {{ ondeck }}
    </div>
  </div>
</template>

<script>
export default {
  name: "Game",
  props: {
    highlighted: { type: Boolean, default: false },
    half: { type: String, default: "" },
    inning: { type: String, default: "1" },
    away: { type: Object, default: () => {} },
    home: { type: Object, default: () => {} },
    outs: { type: String, default: "0" },
    runner_on_first: { type: Boolean, default: false },
    runner_on_second: { type: Boolean, default: false },
    runner_on_third: { type: Boolean, default: false },
    leverage_index: { type: Number, default: 0.0 },
    pitcher: { type: String, default: "" },
    atbat: { type: String, default: "" },
    ondeck: { type: String, default: "" },
    calendar_event_id: { type: String, default: "" }
  },
  computed: {
    basesit() {
      let basesit = 1;
      if (this.runner_on_third) {
        basesit += 4;
      }
      if (this.runner_on_second) {
        basesit += 2;
      }
      if (this.runner_on_first) {
        basesit++;
      }
      return basesit;
    }
  }
};
</script>

<style scoped>
* {
  text-transform: capitalize;
}
</style>

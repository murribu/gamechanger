<template>
  <div
    class="GC-linescore"
    :id="calendar_event_id"
    :class="{ 'GC-game-highlight': highlighted }"
  >
    <table class="table">
      <thead v-if="main_display === 'linescore'">
        <tr>
          <th width="100">{{ status.inning_state + " " + status.inning }}</th>
          <th width="20">R</th>
          <th width="20">H</th>
          <th width="20">E</th>
        </tr>
      </thead>
      <thead v-if="main_display === 'preview'">
        <tr>
          <th width="100">{{ time + " " + time_zone }}</th>
          <th width="60">W - L</th>
        </tr>
      </thead>
      <tbody v-if="main_display === 'linescore'">
        <tr>
          <td>{{ away_name }}</td>
          <td>{{ linescore.r.away }}</td>
          <td>{{ linescore.h.away }}</td>
          <td>{{ linescore.e.away }}</td>
        </tr>
        <tr>
          <td>{{ home_name }}</td>
          <td>{{ linescore.r.home }}</td>
          <td>{{ linescore.h.home }}</td>
          <td>{{ linescore.e.home }}</td>
        </tr>
        <tr>
          <td>Outs: {{ status.o }}</td>
          <td colspan="3" class="center">
            <img
              :src="'images/Bases_' + base_situation.ordinal + '.png'"
              width="34"
              height="11"
              align="absmiddle"
            />
          </td>
        </tr>
      </tbody>
      <tbody v-if="main_display === 'preview'">
        <tr>
          <td>{{ away_name }}</td>
          <td class="center">{{ away_win + "-" + away_loss }}</td>
        </tr>
        <tr>
          <td>{{ home_name }}</td>
          <td class="center">{{ home_win + "-" + home_loss }}</td>
        </tr>
      </tbody>
    </table>
    <div class="GC-game-info" v-if="bottom_display === 'probables'">
      {{
        away_probable_pitcher.first.substring(0, 1) +
          ". " +
          away_probable_pitcher.last +
          " (" +
          away_probable_pitcher.wins +
          "-" +
          away_probable_pitcher.losses +
          ", " +
          away_probable_pitcher.era +
          ")"
      }}<br />
      {{
        home_probable_pitcher.first.substring(0, 1) +
          ". " +
          home_probable_pitcher.last +
          " (" +
          home_probable_pitcher.wins +
          "-" +
          home_probable_pitcher.losses +
          ", " +
          home_probable_pitcher.era +
          ")"
      }}
    </div>
    <div class="GC-game-info" v-if="bottom_display === 'current'">
      Leverage Index: {{ leverage_index.toFixed(2) }}<br />Pitching:
      {{ pitcher ? pitcher.first + " " + pitcher.last : "" }}<br />At Bat:
      {{ batter ? batter.first + " " + batter.last : "" }}<br />
      On Deck: {{ ondeck ? ondeck.first + " " + ondeck.last : "" }}
    </div>
  </div>
</template>

<script>
export default {
  name: "Game",
  props: {
    highlighted: { type: Boolean, default: false },
    status: { type: Object, default: () => {} },
    leverage_index: { type: Number, default: 0.0 },
    base_situation: { type: Object, default: () => {} },
    home_name_abbrev: { type: String, default: "1" },
    away_name_abbrev: { type: String, default: "1" },
    linescore: { type: Object, default: () => {} },
    pitcher: { type: Object, default: () => {} },
    batter: { type: Object, default: () => {} },
    ondeck: { type: Object, default: () => {} },
    calendar_event_id: { type: String, default: "" },
    home_probable_pitcher: { type: Object, default: () => {} },
    away_probable_pitcher: { type: Object, default: () => {} },
    time: { type: String, default: "" },
    time_zone: { type: String, default: "" },
    away_win: { type: String, default: "0" },
    away_loss: { type: String, default: "0" },
    home_win: { type: String, default: "0" },
    home_loss: { type: String, default: "0" },
    away_probable_pitcher: { type: Object, default: () => {} },
    home_probable_pitcher: { type: Object, default: () => {} }
  },
  computed: {
    half_name() {
      let half_name = this.status.top_inning === "Y" ? "top" : "bottom";
      if (this.status.o === 3) {
        half_name = half_name === "top" ? "middle" : "end";
      }
      return half_name;
    },
    home_name() {
      return window.teams.find(t => t.id === this.home_name_abbrev).name;
    },
    away_name() {
      return window.teams.find(t => t.id === this.away_name_abbrev).name;
    },
    main_display() {
      return window.game_status_inds[this.status.ind].main_display;
    },
    bottom_display() {
      return window.game_status_inds[this.status.ind].bottom_display;
    }
  }
};
</script>

<style scoped>
* {
  text-transform: capitalize;
}
</style>

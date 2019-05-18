<template>
  <div
    class="GC-linescore"
    :id="calendar_event_id"
    :class="{ 'GC-game-highlight': highlighted }"
  >
    <table class="table">
      <thead v-if="main_display === 'linescore' || main_display === 'final'">
        <tr>
          <th width="100">
            {{
              main_display === "linescore"
                ? linescore.inningState + " " + linescore.currentInning
                : "Final"
            }}
          </th>
          <th width="20">R</th>
          <th width="20">H</th>
          <th width="20">E</th>
        </tr>
      </thead>
      <thead v-if="main_display === 'preview'">
        <tr>
          <th width="100">{{ gameDate }}</th>
          <th width="60">W - L</th>
        </tr>
      </thead>
      <tbody v-if="main_display === 'linescore' || main_display === 'final'">
        <tr>
          <td>{{ away_name }}</td>
          <td>{{ linescore.teams.away.runs }}</td>
          <td>{{ linescore.teams.away.hits }}</td>
          <td>{{ linescore.teams.away.errors }}</td>
        </tr>
        <tr>
          <td>{{ home_name }}</td>
          <td>{{ linescore.teams.home.runs }}</td>
          <td>{{ linescore.teams.home.hits }}</td>
          <td>{{ linescore.teams.home.errors }}</td>
        </tr>
        <tr v-if="main_display === 'linescore'">
          <td>Outs: {{ linescore.outs }}</td>
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
          <td class="center">
            {{
              teams.away.leagueRecord.wins +
                "-" +
                teams.away.leagueRecord.losses
            }}
          </td>
        </tr>
        <tr>
          <td>{{ home_name }}</td>
          <td class="center">
            {{
              teams.home.leagueRecord.wins +
                "-" +
                teams.home.leagueRecord.losses
            }}
          </td>
        </tr>
      </tbody>
    </table>
    <div class="GC-game-info" v-if="bottom_display === 'result' && decisions">
      W: {{ decisions.winner.fullName }} <br />
      L: {{ decisions.loser.fullName }} <br />
      {{ decisions.save ? "S: " + decisions.save.fullName : "" }}
    </div>
    <div class="GC-game-info" v-if="bottom_display === 'current'">
      Leverage Index: {{ leverage_index.toFixed(2) }}<br />Pitching:
      {{ linescore ? linescore.offense.pitcher.fullName : "" }}<br />At Bat:
      {{ linescore ? linescore.offense.batter.fullName : "" }}<br />
      On Deck: {{ linescore ? linescore.offense.onDeck.fullName : "" }}
    </div>
  </div>
</template>

<script>
import moment from "moment";
export default {
  name: "Game",
  props: {
    highlighted: { type: Boolean, default: false },
    status: { type: Object, default: () => {} },
    leverage_index: { type: Number, default: 0.0 },
    base_situation: { type: Object, default: () => {} },
    linescore: { type: Object, default: () => {} },
    calendar_event_id: { type: String, default: "" },
    "game-date": { type: String, default: "" },
    teams: { type: Object, default: () => {} },
    decisions: { type: Object, default: () => {} }
  },
  computed: {
    half_name() {
      let half_name = this.linescore.isTopInning === "true" ? "top" : "bottom";
      if (this.linescore.outs === 3) {
        half_name = half_name === "top" ? "middle" : "end";
      }
      return half_name;
    },
    home_name() {
      return window.teams.find(t => t.id === this.teams.home.team.id).name;
    },
    away_name() {
      return window.teams.find(t => t.id === this.teams.away.team.id).name;
    },
    main_display() {
      return window.game_status_inds[this.status.codedGameState].main_display;
    },
    bottom_display() {
      return window.game_status_inds[this.status.codedGameState].bottom_display;
    },
    game_time() {
      return moment(this["game-date"]).format("LT");
    }
  }
};
</script>

<style scoped>
* {
  text-transform: capitalize;
}
</style>

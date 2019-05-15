<template>
  <div>
    <div class="GC-save-settings">
      <button id="launch">Launch Video</button>
    </div>
    <br />
    <div class="GC-notes">
      <a href="#FAQ">Requirements / Instructions</a>
    </div>
    <br />
    <div id="current_game">{{ current_game }}</div>

    <div class="GC-settings">
      <h3 class="GC-settings-title">Games</h3>
      <div class="GC-games">
        <Game v-for="game in ordered_filtered_games" v-bind="game"></Game>
      </div>
    </div>
  </div>
</template>

<script>
import Game from "./components/Game.vue";
export default {
  data() {
    return {
      games: [
        {
          half: "bottom",
          highlighted: false,
          inning: 5,
          home: { name: "Athletics", runs: 3, hits: 4, errors: 0 },
          away: { name: "Mariners", runs: 4, hits: 6, errors: 1 },
          outs: 2,
          runner_on_first: true,
          runner_on_second: false,
          runner_on_third: true,
          leverage_index: 0.41,
          pitching: "Mike Leake",
          atbat: "Ramon Laureano",
          ondeck: "Robbie Grossman",
          calendar_event_id: "14-566391-2019-05-14"
        }
      ],
      game_vid: "",
      game_pk: ""
    };
  },
  computed: {
    current_game() {
      console.log("current_game NOT YET IMPLEMENTED");
      return "";
    },
    ordered_filtered_games() {
      console.log("ordered_filtered_games NOT YET IMPLEMENTED");
      return this.games;
    }
  },
  components: { Game }
};
</script>

<style scoped></style>

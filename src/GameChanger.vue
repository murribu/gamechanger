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
        <Game
          v-for="(game, key) in ordered_filtered_games"
          v-bind="game"
          :highlighted="game.game_pk === current_game"
          :key="key"
        ></Game>
      </div>
    </div>
    <br />
    <div class="GC-container">
      <div class="GC-column">
        <div class="GC-settings">
          <h3 class="GC-settings-title">
            If Batter is On Deck with &lt; 2 Outs
          </h3>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              value="Y"
              id="on_deck_Y"
              v-model="ondeck"
            />
            <label for="on_deck_Y"
              ><span></span> Switch to game immediately</label
            >
          </div>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              value="N"
              id="on_deck_N"
              v-model="ondeck"
            />
            <label for="on_deck_N"
              ><span></span> Wait until player is at bat</label
            >
          </div>
        </div>
        <div class="GC-settings">
          <h3 class="GC-settings-title">Teams to Ignore</h3>
          <div class="league-ignore-container">
            <div class="league-ignore">
              <div
                class="team-ignore"
                v-for="team in ordered_teams_by_league('NL')"
              >
                <input
                  type="checkbox"
                  class="form__checkbox"
                  :name="'x' + team.id"
                  :id="'x' + team.id"
                  :value="true"
                  v-model="team.ignore"
                />
                <label :for="'x' + team.id"><span></span> {{ team.id }}</label>
              </div>
            </div>
            <div class="league-ignore">
              <div
                class="team-ignore"
                v-for="team in ordered_teams_by_league('AL')"
              >
                <input
                  type="checkbox"
                  class="form__checkbox"
                  :name="'x' + team.id"
                  :id="'x' + team.id"
                  :value="true"
                  v-model="team.ignore"
                />
                <label :for="'x' + team.id"><span></span>{{ team.id }}</label>
              </div>
            </div>
          </div>
        </div>
        <div class="GC-settings">
          <h3 class="GC-settings-title">Championship Leverage Index</h3>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              id="include_CLI_Y"
              name="include_CLI"
              value="Y"
              v-model="include_CLI"
            />
            <label for="include_CLI_Y"
              ><span></span> Include in Leverage Index</label
            >
          </div>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              id="include_CLI_N"
              name="include_CLI"
              value="N"
              v-model="include_CLI"
            />
            <label for="include_CLI_N"
              ><span></span> Do not include in Leverage Index</label
            >
          </div>
        </div>
        <div class="GC-settings">
          <h3 class="GC-settings-title">Delay</h3>
          <select
            class="form__dropdown-select"
            id="delay"
            name="delay"
            v-model="delay"
          >
            <option :value="n" v-for="n in 75">{{ n }} seconds</option>
          </select>
          <br />
          * Time between MLB's gamday feed
          <br />
          and MLB.tv broadcast
        </div>
        <div class="GC-settings">
          <h3 class="GC-settings-title">Update Frequency</h3>
          <select
            class="form__dropdown-select"
            id="interval_frequency"
            name="interval_frequency"
            v-model="interval_frequency"
          >
            <option :value="n" v-for="n in 10">{{ n * 6 }} seconds</option>
          </select>
          <br />
          * How often the games update
        </div>
        <div class="GC-settings">
          <h3 class="GC-settings-title">Video Player</h3>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              id="vid_player_reg"
              name="vid_player"
              value="reg"
              v-model="vid_player"
            />
            <label for="vid_player_reg"
              ><span></span> Regular MLB.tv video player</label
            >
          </div>
          <div class="team-ignore">
            <input
              type="radio"
              class="form__radio"
              id="vid_player_old"
              name="vid_player"
              value="old"
              v-model="vid_player"
            />
            <label for="vid_player_old"
              ><span></span> Old MLB.tv video player</label
            >
          </div>
        </div>
      </div>
      <div class="GC-column">
        <div class="GC-settings">
          <h3 class="GC-settings-title">Priority List</h3>
          <div class="GC-title-container">
            <span class="GC-priorityNumber">#</span>
            <div class="GC-title">
              <div class="GC-drag__element-title">
                <div class="GC-priority__type-container"><h4>Type</h4></div>
                <div class="GC-priority__data-container">
                  <h4>Player/Team/Data</h4>
                </div>
                <div class="GC-priority__immediate-container">
                  <h4>Switch<br />Immediately?</h4>
                </div>
              </div>
            </div>
          </div>
          <draggable v-model="priorities">
            <div
              class="GC-priorityContainer"
              v-for="(priority, key) in priorities"
            >
              <span class="GC-priorityNumber">{{ key + 1 }}</span>
              <div class="drag__container" :id="'drag_container' + (key + 1)">
                <div class="xdrag-element">
                  <div class="GC-priority__type-container">
                    <select
                      class="form__dropdown-select"
                      v-model="priority.type"
                    >
                      <option value=""></option>
                      <option value="bat">Batter</option>
                      <option value="pit">Pitcher</option>
                      <option value="run">Runner</option>
                      <option value="LI">Leverage Index</option>
                      <option value="team">Team</option>
                      <option value="NoNo">No-Hitter</option>
                      <option value="GameSit">Game Situation</option>
                      <option value="team_bat">Team Batting</option>
                      <option value="team_pit">Team Pitching</option>
                      <option value="Misc">Miscellaneous</option>
                    </select>
                  </div>
                  <div class="GC-priority__data-container">
                    <select
                      v-model="priority.object"
                      class="form__dropdown-select"
                    >
                      <option
                        :value="object.id"
                        v-for="object in priorityObjects(priority.type)"
                        >{{ object.display }}</option
                      >
                    </select>
                  </div>
                  <div class="GC-priority__immediate-container">
                    <input
                      type="checkbox"
                      class="form__checkbox"
                      v-model="priority.switch_immediately"
                      :id="'immediate_' + (key + 1)"
                      :name="'immediate_' + (key + 1)"
                    />
                    <label :for="'immediate_' + (key + 1)"><span></span></label>
                  </div>
                  <svg
                    class="drag__delete"
                    @click.prevent="deletePriority(key)"
                  >
                    <use xlink:href="images/sprite.svg#icon-bin"></use>
                  </svg>
                  <svg class="drag__handle">
                    <use xlink:href="images/sprite.svg#icon-menu2"></use>
                  </svg>
                </div>
              </div>
            </div>
          </draggable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from "vue";
import Game from "./components/Game.vue";
import draggable from "vuedraggable";
import axios from "axios";
import { get_base_situation } from "./helpers";

// ***** TODO ***** REMOVE THIS BEFORE RELEASING
import "./style.css";
// ***** TODO ***** REMOVE THIS BEFORE RELEASING

export default {
  data() {
    return {
      teams: [],
      games: [],
      ondeck: "N",
      include_CLI: "N",
      delay: 5,
      vid_player: "reg",
      priorities: [{ type: "", object: "", switch_immediately: false }],
      cur_game_vid: "",
      interval: null,
      interval_frequency: 1 // each unit is 6 seconds
    };
  },
  watch: {
    interval_frequency: function(val, oldVal) {
      clearInterval(this.interval);
      this.interval = setInterval(() => {
        this.updateGames();
      }, this.interval_frequency * 6000);
      localStorage.setItem("interval_frequency", val);
    },
    ondeck: {
      handler: function(val, oldVal) {
        localStorage.setItem("ondeck", val);
      }
    },
    include_CLI: {
      handler: function(val, oldVal) {
        localStorage.setItem("include_CLI", val);
      }
    },
    delay: {
      handler: function(val, oldVal) {
        localStorage.setItem("delay", val);
      }
    },
    vid_player: {
      handler: function(val, oldVal) {
        localStorage.setItem("vid_player", val);
      }
    },
    priorities: {
      handler: function(val, oldVal) {
        if (
          val[val.length - 1].type !== "" ||
          val[val.length - 1].object !== "" ||
          val[val.length - 1].switch_immediately !== false
        ) {
          this.priorities.push({
            type: "",
            object: "",
            switch_immediately: false
          });
        }
        localStorage.setItem("priorities", JSON.stringify(val));
      },
      deep: true
    },
    teams: {
      handler: function(val, oldVal) {
        localStorage.setItem(
          "ignoredTeams",
          JSON.stringify(
            val.reduce((accumulator, current_team) => {
              if (current_team.ignore) {
                accumulator.push(current_team.id);
              }
              return accumulator;
            }, [])
          )
        );
      },
      deep: true
    }
  },
  mounted() {
    let ignoredTeams = [];
    if (localStorage.getItem("ignoredTeams")) {
      ignoredTeams = JSON.parse(localStorage.getItem("ignoredTeams"));
    }
    this.teams = [...window.teams].map(t => {
      t.ignore = ignoredTeams.indexOf(t.id) > -1;
      return t;
    });
    if (localStorage.getItem("priorities")) {
      this.priorities = JSON.parse(localStorage.getItem("priorities"));
    }
    if (localStorage.getItem("ondeck")) {
      this.ondeck = localStorage.getItem("ondeck");
    }
    if (localStorage.getItem("include_CLI")) {
      this.include_CLI = localStorage.getItem("include_CLI");
    }
    if (localStorage.getItem("delay")) {
      this.delay = localStorage.getItem("delay");
    }
    if (localStorage.getItem("vid_player")) {
      this.vid_player = localStorage.getItem("vid_player");
    }
    if (localStorage.getItem("interval_frequency")) {
      this.interval_frequency = localStorage.getItem("interval_frequency");
    }
    this.updateGames();
    this.interval = setInterval(() => {
      this.updateGames();
    }, this.interval_frequency * 6000);
  },
  methods: {
    ordered_teams_by_league(league) {
      return this.teams
        .filter(t => t.league === league)
        .sort((a, b) => (a.abbr > b.abbr ? -1 : 1));
    },
    priorityObjects(type) {
      var retVal = [];
      // var type = "bat";
      switch (type) {
        case "bat":
        case "pit":
        case "run":
          retVal = window.players;
          break;
        case "LI":
          retVal = window.leverage_index_thresholds;
          break;
        case "team":
        case "team_bat":
        case "team_pit":
          retVal = window.teams;
          break;
        case "NoNo":
          retVal = window.NoNo;
          break;
        case "GameSit":
          retVal = window.GameSit;
          break;
        case "Misc":
          retVal = window.Misc;
          break;
      }
      return retVal;
    },
    deletePriority(key) {
      if (confirm("Delete priority?")) {
        this.priorities.splice(key, 1);
      }
    },
    updateGames() {
      const user_date = new Date();
      const user_time = user_date.getTime();
      const user_offset = user_date.getTimezoneOffset() * 60000;
      const utc = user_time + user_offset;
      const western_time = utc + 3600000 * -8;
      const date = new Date(western_time);

      const day = ("0" + date.getDate()).slice(-2);
      const month = ("0" + (date.getMonth() + 1)).slice(-2);
      const year = date.getFullYear();
      const url =
        "//gd2.mlb.com/components/game/mlb/year_" +
        year +
        "/month_" +
        month +
        "/day_" +
        day +
        "/master_scoreboard.json?v=" +
        new Date().getTime();

      axios.get(url).then(({ data }) => {
        const games_from_mlb = data.data.games.game;
        const active_inning_states = ["Top", "Bottom"];
        console.log(games_from_mlb);
        let games = [];
        for (
          var gameIndex = 0;
          gameIndex < games_from_mlb.length;
          gameIndex++
        ) {
          const game = games_from_mlb[gameIndex];
          game.base_situation = get_base_situation(
            game.runners_on_base ? game.runners_on_base.status : null
          );
          const championship_leverage_index =
            this.include_CLI === "Y"
              ? window.games_CLI[game.home_name_abbrev]
              : 1;
          game.leverage_index =
            game.status.inning && game.linescore
              ? championship_leverage_index *
                window.LI[
                  Math.min(game.status.inning, 9).toString() +
                    (game.status.top_inning === "Y" ? 1 : 2).toString() +
                    game.base_situation.ordinal.toString() +
                    game.status.o
                ][
                  Math.min(
                    Math.max(
                      parseInt(game.linescore.r.home) -
                        parseInt(game.linescore.r.away),
                      -10
                    ),
                    10
                  )
                ]
              : 0;
          game.base_situation = get_base_situation(
            game.runners_on_base ? game.runners_on_base.status : null
          );
          games.push(game);
        }
        Vue.set(this, "games", games);
        console.log(games);
      });
    },
    sortGames(game1, game2) {
      if (
        window.game_status_inds[game1.status.ind].sort_score ===
        window.game_status_inds[game2.status.ind].sort_score
      ) {
        if (game1.leverage_index && game2.leverage_index) {
          return game1.leverage_index > game2.leverage_index ? -1 : 1;
        }
      } else {
        return window.game_status_inds[game1.status.ind].sort_score >
          window.game_status_inds[game2.status.ind].sort_score
          ? -1
          : 1;
      }
    }
  },
  computed: {
    current_game() {
      console.log("current_game NOT YET IMPLEMENTED");
      return "";
    },
    ordered_filtered_games() {
      const missed_statuses = this.games.filter(
        g => typeof window.game_status_inds[g.status.ind] === "undefined"
      );
      if (missed_statuses.length > 0) {
        console.log("MISSED STATUS", missed_statuses);
      }
      return this.games
        .filter(
          game =>
            window.game_status_inds[game.status.ind].main_display !==
              "linescore" ||
            game.status.inning_state === "Top" ||
            game.status.inning_state === "Bottom"
        )
        .sort(this.sortGames);
    }
  },
  components: { Game, draggable }
};
</script>

<style scoped>
.xdrag-element {
  display: inline-block;
  background-color: #eee;
  border: 0.1rem solid #ccc;
  border-radius: 0.2rem;
  cursor: move;
  padding: 0.5rem;
}
</style>

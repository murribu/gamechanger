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
                  <svg class="drag__delete">
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
import Game from "./components/Game.vue";
import draggable from "vuedraggable";

// ***** TODO ***** REMOVE THIS BEFORE RELEASING
import "./style.css";
// ***** TODO ***** REMOVE THIS BEFORE RELEASING

export default {
  data() {
    return {
      teams: [],
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
      ondeck: "N",
      include_CLI: "N",
      delay: 5,
      vid_player: "reg",
      priorities: [{ type: "", object: "", switch_immediately: false }]
    };
  },
  watch: {
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
      },
      deep: true
    }
  },
  mounted() {
    this.teams = [...window.teams];
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
          retVal = window.LI;
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
      return retVal.sort((a, b) => (a.display > b.display ? 1 : -1));
    }
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

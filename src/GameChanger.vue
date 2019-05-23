<template>
  <div>
    <div class="GC-save-settings">
      <button id="launch" @click.prevent="launch" v-if="!video_launched">
        Launch Video
      </button>
    </div>
    <br />
    <div class="GC-notes">
      <a href="#FAQ">Requirements / Instructions</a>
    </div>
    <br />
    <div id="current_game" v-if="current_game && video_launched">
      <div class="GC-current-game">
        <h3 class="GC-settings-title">Currently Showing</h3>
        {{ currentlyShowing }}<br /><span
          v-if="reason_priority.type === 'no-preference-items-were-met'"
          >Highest leverage game<br />No priority items were met</span
        ><span v-if="reason_priority.type !== 'no-preference-items-were-met'"
          >{{ currentPriorityDescription }}<br />Priority #{{
            currentPriorityIndex + 1
          }}</span
        >
      </div>
    </div>

    <div class="GC-settings">
      <h3 class="GC-settings-title">Games</h3>
      <div class="GC-games">
        <Game
          v-for="(game, key) in ordered_filtered_games"
          v-bind="game"
          :highlighted="video_launched && game.gamePk === current_game.gamePk"
          :probable_pitchers="probable_pitchers[game.gamePk]"
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
                <label :for="'x' + team.id"
                  ><span></span> {{ team.tbg_abbr }}</label
                >
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
                <label :for="'x' + team.id"
                  ><span></span> {{ team.tbg_abbr }}</label
                >
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
            id="update_games_interval_frequency"
            name="update_games_interval_frequency"
            v-model="update_games_interval_frequency"
          >
            <option :value="n" v-for="n in 10">{{ n * 6 }} seconds</option>
          </select>
          <br />
          * How often the game data updates
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
          <div class="GC-settings-title">Video Feed Priority</div>
          <draggable v-model="feed_priorities">
            <div
              class="GC-prioirtyContainer"
              v-for="(feed, key) in feed_priorities"
            >
              <span>{{ key + 1 }}</span>
              <div
                class="drag__container"
                :id="'feed_drag_container' + (key + 1)"
              >
                <div
                  class="xdrag-element"
                  :class="{ inactive: !video_feeds[feed.id] }"
                >
                  <div class="GC-priority__type-container">
                    {{ feed.tbg_abbr }}
                  </div>
                </div>
              </div>
            </div>
          </draggable>
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
                  <a
                    href="#"
                    style="font-size: small;white-space: nowrap;"
                    @click.prevent="toggleSwitchImmediately"
                  >
                    {{
                      priorities
                        .slice(0, priorities.length - 1)
                        .filter(p => !p.immediate).length === 0
                        ? "Deselect"
                        : "Select"
                    }}
                    All
                  </a>
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
                      v-model="priority.data"
                      class="form__dropdown-select"
                    >
                      <option
                        :value="data.id"
                        v-for="data in priorityDataForDropdown(priority.type)"
                        >{{ data.display }}</option
                      >
                    </select>
                  </div>
                  <div class="GC-priority__immediate-container">
                    <input
                      type="checkbox"
                      class="form__checkbox"
                      v-model="priority.immediate"
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
      delay: 5, // each unit is 1 second
      vid_player: "reg",
      priorities: [{ type: "", data: "", immediate: false }],
      update_games_interval: null,
      update_games_interval_frequency: 1, // each unit is 6 seconds
      current_game: false,
      reason_priority: null,
      check_child_interval: null,
      check_child_interval_frequency: 500, // each unit is 1 millisecond
      video_launched: false,
      probable_pitchers: {},
      feed_priorities: [],
      video_feeds: {}
    };
  },
  watch: {
    update_games_interval_frequency: function(val, oldVal) {
      clearInterval(this.update_games_interval);
      this.update_games_interval = setInterval(() => {
        this.updateGames();
      }, this.update_games_interval_frequency * 6000);
      localStorage.setItem("update_games_interval_frequency", val);
    },
    ondeck: {
      handler: function(val, oldVal) {
        localStorage.setItem("GC-on_deck", val);
      }
    },
    include_CLI: {
      handler: function(val, oldVal) {
        localStorage.setItem("GC-CLI", val);
      }
    },
    delay: {
      handler: function(val, oldVal) {
        localStorage.setItem("GC-delay", val);
      }
    },
    vid_player: {
      handler: function(val, oldVal) {
        localStorage.setItem("GC-vid", val);
      }
    },
    priorities: {
      handler: function(val, oldVal) {
        if (
          val[val.length - 1].type !== "" ||
          val[val.length - 1].data !== "" ||
          val[val.length - 1].immediate !== false
        ) {
          this.priorities.push({
            type: "",
            data: "",
            immediate: false
          });
        }
        localStorage.setItem("GC-priority", JSON.stringify(val));
        this.findCurrentGame();
      },
      deep: true
    },
    feed_priorities: {
      handler: function(val, oldVal) {
        localStorage.setItem("GC-feed_priority", JSON.stringify(val));
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
    },
    current_game: {
      handler: function(val, oldVal) {
        if (val.gamePk !== oldVal.gamePk) {
          this.setGameWindowUrl();
        }
      },
      deep: true
    },
    games: {
      handler: function(val, oldVal) {
        this.findCurrentGame();
      },
      deep: true
    }
  },
  mounted() {
    let ignoredTeams = [];
    if (localStorage.getItem("ignoredTeams")) {
      ignoredTeams = JSON.parse(localStorage.getItem("ignoredTeams"));
    }
    Vue.nextTick(() => {
      this.teams = [...window.teams].map(t => {
        t.ignore = ignoredTeams.indexOf(t.id) > -1;
        return t;
      });
      if (localStorage.getItem("GC-feed_priority")) {
        this.feed_priorities = JSON.parse(
          localStorage.getItem("GC-feed_priority")
        );
      } else {
        this.feed_priorities = [...window.teams];
      }
    });
    if (localStorage.getItem("GC-priority")) {
      this.priorities = JSON.parse(localStorage.getItem("GC-priority")).map(
        p => {
          // Convert empty string to `false`, "true" to `true`
          p.immediate = !!p.immediate;
          return p;
        }
      );
    }
    if (localStorage.getItem("GC-on_deck")) {
      this.ondeck = localStorage.getItem("GC-on_deck");
    }
    if (localStorage.getItem("GC-CLI")) {
      this.include_CLI = localStorage.getItem("GC-CLI");
    }
    if (localStorage.getItem("GC-delay")) {
      this.delay = localStorage.getItem("GC-delay");
      if (this.delay > 999) {
        this.delay /= 1000;
      }
    }
    if (localStorage.getItem("GC-vid")) {
      this.vid_player = localStorage.getItem("GC-vid");
    }
    if (localStorage.getItem("update_games_interval_frequency")) {
      this.update_games_interval_frequency = localStorage.getItem(
        "update_games_interval_frequency"
      );
    }
    this.getProbablePitchers();
    this.getVideoFeeds();
    setInterval(() => {
      this.getVideoFeeds();
    }, 1000 * 60 * 5); // get the video feeds every 5 minutes
    this.updateGames();
    this.update_games_interval = setInterval(() => {
      this.updateGames();
    }, this.update_games_interval_frequency * 6000);
  },
  methods: {
    getProbablePitchers() {
      const user_date = new Date();
      const user_time = user_date.getTime();
      const user_offset = user_date.getTimezoneOffset() * 60000;
      const utc = user_time + user_offset;
      const western_time = utc + 3600000 * -8;
      const date = new Date(western_time);

      const day = ("0" + date.getDate()).slice(-2);
      const month = ("0" + (date.getMonth() + 1)).slice(-2);
      const year = date.getFullYear();
      const url = `https://statsapi.mlb.com/api/v1/schedule?language=en&sportId=1&date=${month}/${day}/${year}&sortBy=gameDate&hydrate=probablePitcher`;
      axios.get(url).then(({ data }) => {
        const games_from_mlb = data.dates[0].games;
        for (
          var gameIndex = 0;
          gameIndex < games_from_mlb.length;
          gameIndex++
        ) {
          let game = games_from_mlb[gameIndex];
          this.probable_pitchers[game.gamePk] = {
            away: game.teams.away.probablePitcher
              ? game.teams.away.probablePitcher.fullName
              : "TBD",
            home: game.teams.home.probablePitcher
              ? game.teams.home.probablePitcher.fullName
              : "TBD"
          };
        }
      });
    },
    ordered_teams_by_league(league) {
      return this.teams
        .filter(t => t.league === league)
        .sort((a, b) => (a.abbr > b.abbr ? -1 : 1));
    },
    priorityDataForDropdown(type) {
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
      const url = `https://statsapi.mlb.com/api/v1/schedule?language=en&sportId=1&date=${month}/${day}/${year}&sortBy=gameDate&hydrate=linescore(matchup,runners),decisions`;

      axios.get(url).then(({ data }) => {
        const games_from_mlb = data.dates[0].games;
        let games = [];
        for (
          var gameIndex = 0;
          gameIndex < games_from_mlb.length;
          gameIndex++
        ) {
          const game = games_from_mlb[gameIndex];
          game.base_situation = get_base_situation(
            game.linescore ? game.linescore.offense : null
          );
          const championship_leverage_index =
            this.include_CLI === "Y"
              ? window.games_CLI[
                  window.teams.find(t => t.id === game.teams.home.team.id)
                    .tbg_abbr
                ]
              : 1;
          game.leverage_index =
            game.linescore &&
            game.linescore.currentInning &&
            window.game_status_inds[game.status.codedGameState]
              .bottom_display === "current" &&
            game.linescore.outs < 3
              ? championship_leverage_index *
                window.LI[
                  Math.min(game.linescore.currentInning, 9).toString() +
                    (game.linescore.isTopInning ? 1 : 2).toString() +
                    game.base_situation.ordinal.toString() +
                    game.linescore.outs
                ][
                  Math.min(
                    Math.max(
                      parseInt(game.linescore.teams.home.runs) -
                        parseInt(game.linescore.teams.away.runs),
                      -10
                    ),
                    10
                  )
                ]
              : 0;
          games.push(game);
        }
        Vue.set(this, "games", games);
        console.log(games);
      });
    },
    sortGames(game1, game2) {
      if (
        window.game_status_inds[game1.status.codedGameState].sort_score ===
        window.game_status_inds[game2.status.codedGameState].sort_score
      ) {
        if (game1.leverage_index && game2.leverage_index) {
          return game1.leverage_index > game2.leverage_index ? -1 : 1;
        }
      } else {
        return window.game_status_inds[game1.status.codedGameState].sort_score >
          window.game_status_inds[game2.status.codedGameState].sort_score
          ? -1
          : 1;
      }
    },
    launch() {
      this.video_launched = true;
      this.game_window = window.open("", "mlb.tv");
      this.setGameWindowUrl();
      this.check_child_interval = setInterval(
        () => this.checkChild(),
        this.check_child_interval_frequency
      );
    },
    setGameWindowUrl() {
      let game_url = "";
      if (this.vid_player === "old") {
        game_url =
          "http://mlb.mlb.com/shared/flash/mediaplayer/v4.5/R8/MP4.jsp?calendar_event_id=" +
          this.current_game.calendarEventId +
          "&media_id=&view_key=&media_type=video&source=MLB&sponsor=MLB&clickOrigin=Media+Grid&affiliateId=Media+Grid&team=mlb";
      } else {
        let index = Math.min(
          this.feed_priorities.findIndex(
            fp => fp.id == this.current_game.teams.away.team.id
          ),
          this.feed_priorities.findIndex(
            fp => fp.id == this.current_game.teams.home.team.id
          )
        );
        let feed = this.video_feeds[this.feed_priorities[index].id];
        game_url = "https://www.mlb.com/tv/g" + this.current_game.gamePk;
        if (feed) {
          game_url += "/v" + feed.contentId;
        }
      }

      setTimeout(
        () =>
          this.game_window && this.video_launched
            ? (this.game_window.location.href = game_url)
            : null,
        this.delay * 1000
      );
    },
    checkChild() {
      if (this.game_window.closed) {
        this.video_launched = false;
        clearInterval(this.check_child_interval);
      }
    },
    findCurrentGame() {
      let self = this;
      let current_game = false;
      let reason_priority = false;

      // Checks if any games match priority list
      priority_loop: for (
        var priorityIndex = 0;
        priorityIndex < this.priorities.length;
        priorityIndex++
      ) {
        let priority = this.priorities[priorityIndex];
        if (
          !this.current_game ||
          (this.games.find(g => g.gamePk === self.current_game.gamePk) &&
            this.games.find(g => g.gamePk === self.current_game.gamePk)
              .linescore.offense.batter.id !==
              this.current_game.linescore.offense.batter.id) ||
          priority.immediate
        ) {
          for (var gameIndex = 0; gameIndex < this.games.length; gameIndex++) {
            let game = this.games[gameIndex];
            if (this.gameAndPriorityMatch(game, priority)) {
              current_game = { ...game };
              reason_priority = { ...priority };
              break priority_loop;
            }
          }
        }
      }
      // if no preference items were met
      if (!current_game) {
        let ordered_filtered_games = this.ordered_filtered_games.filter(
          g =>
            this.teams.find(t => t.id == g.teams.away.team.id).ignore ===
              false &&
            this.teams.find(t => t.id == g.teams.home.team.id).ignore === false
        );
        if (
          ordered_filtered_games.length > 0 &&
          ((!this.reason_priority ||
            this.reason_priority.type === "no-preference-items-were-met") &&
            (!this.games.find(g => g.gamePk === this.current_game.gamePk) ||
              ordered_filtered_games[0].leverage_index >
                this.games.find(g => g.gamePk === this.current_game.gamePk)
                  .leverage_index +
                  0.5))
        ) {
          current_game =
            ordered_filtered_games.length > 0
              ? { ...ordered_filtered_games[0] }
              : null;
          reason_priority = { type: "no-preference-items-were-met" };
        }
      }

      if (current_game) {
        this.current_game = current_game;
        this.reason_priority = reason_priority;
      }
    },
    gameAndPriorityMatch(game, priority) {
      if (
        this.teams.find(t => t.id == game.teams.away.team.id).ignore ||
        this.teams.find(t => t.id == game.teams.home.team.id).ignore ||
        window.game_status_inds[game.status.codedGameState].bottom_display !==
          "current"
      ) {
        return false;
      }
      switch (priority.type) {
        case "bat":
          return (
            (!!game.linescore &&
              !!game.linescore.offense &&
              !!game.linescore.offense.batter &&
              priority.data == game.linescore.offense.batter.id) ||
            (!!game.linescore &&
              !!game.linescore.offense &&
              !!game.linescore.ondeck &&
              priority.data == game.linescore.offense.ondeck.id &&
              this.ondeck === "Y" &&
              game.linescore.outs < 2)
          );
        case "pit":
          return (
            !!game.linescore &&
            !!game.linescore.defense &&
            !!game.linescore.defense.pitcher &&
            priority.data === game.linescore.defense.pitcher.id
          );
        case "run":
          return (
            !!game.linescore &&
            !!game.linescore.offense &&
            ((game.linescore.offense.first &&
              priority.data == game.linescore.offense.first.id) ||
              (game.linescore.offense.second &&
                priority.data == game.linescore.offense.second.id) ||
              (game.linescore.offense.third &&
                priority.data == game.linescore.offense.third.id))
          );
        case "team":
          return (
            !!game.teams &&
            !!game.teams.away &&
            !!game.teams.home &&
            (priority.data == game.teams.away.team.id ||
              priority.data == game.teams.home.team.id)
          );
        case "team_bat":
          return (
            !!game.teams &&
            !!game.teams.away &&
            !!game.teams.home &&
            ((priority.data == game.teams.away.team.id &&
              game.linescore.inningHalf === "Top") ||
              (priority.data == game.teams.home.team.id &&
                game.linescore.inningHalf === "Bottom"))
          );
        case "team_pit":
          return (
            !!game.teams &&
            !!game.teams.away &&
            !!game.teams.home &&
            ((priority.data == game.teams.home.team.id &&
              game.linescore.inningHalf === "Top") ||
              (priority.data == game.teams.away.team.id &&
                game.linescore.inningHalf === "Bottom"))
          );
        case "LI":
          return game.leverage_index >= priority.data;
        case "NoNo":
          return (
            !!game.linescore &&
            (game.linescore.currentInning > parseInt(priority.data) &&
              ((game.linescore.inningHalf === "Top" &&
                game.linescore.teams.away.hits === 0) ||
                (game.linescore.inningHalf === "Bottom" &&
                  game.linescore.teams.home.hits === 0)))
          );
        case "GameSit":
          let inning = parseInt(priority.data.substring(7, 1));
          if (priority.data.substring(-3) === "tie") {
            return (
              !!game.linescore &&
              !!game.linescore.teams &&
              (game.linescore.teams.away.runs ===
                game.linescore.teams.home.runs &&
                game.linescore.currentInning > inning)
            );
          } else if (priority.data.substring(-3) === "run") {
            return (
              !!game.linescore &&
              !!game.linescore.teams &&
              (Math.abs(
                game.linescore.teams.away.runs - game.linescore.teams.home.runs
              ) <= 1 &&
                game.linescore.currentInning > inning)
            );
          } else {
            console.log("Bad Game Situation ", priority);
          }
        case "Misc":
          switch (priority.data) {
            case "PosP_pit":
              return (
                !!game.linescore &&
                !!game.linescore.defense &&
                !!game.linescore.defense.pitcher &&
                window.posPlayers[game.linescore.defense.pitcher.id] === "PosP"
              );
            case "extra":
              return (
                game.linescore &&
                game.linescore.currentInning > 9 &&
                window.game_status_inds[game.status.codedGameState]
                  .bottom_display === "current"
              );
            case "replay":
              return (
                !!window.game_status_inds[game.status.codedGameState] &&
                window.game_status_inds[game.status.codedGameState].challenge
              );
            case "21Ks":
              console.log("21Ks NOT YET IMPLEMENTED");
              //if a starting pitcher has gone at least four innings, struck out more than two batters per inning, and his pitch count is no more than six times his strikeout total
              return;
          }
        case "":
          return;
        default:
          console.log(
            "Priority Type " + priority.type + ": NOT YET IMPLEMENTED"
          );
          return false;
          break;
      }
    },
    getVideoFeeds() {
      const user_date = new Date();
      const user_time = user_date.getTime();
      const user_offset = user_date.getTimezoneOffset() * 60000;
      const utc = user_time + user_offset;
      const western_time = utc + 3600000 * -8;
      const date = new Date(western_time);

      const day = ("0" + date.getDate()).slice(-2);
      const month = ("0" + (date.getMonth() + 1)).slice(-2);
      const year = date.getFullYear();
      let self = this;
      const url = `https://statsapi.mlb.com/api/v1/schedule?language=en&sportId=1&date=${month +
        "/" +
        day +
        "/" +
        year}&sortBy=gameDate&hydrate=game(content(media(epg)))`;
      axios.get(url).then(({ data }) => {
        const games_from_mlb = data.dates[0].games;
        for (
          var gameIndex = 0;
          gameIndex < games_from_mlb.length;
          gameIndex++
        ) {
          let game = games_from_mlb[gameIndex];
          if (
            game.content &&
            game.content.media &&
            game.content.media.epg &&
            game.content.media.epg.find(e => e.title === "MLBTV") &&
            window.game_status_inds[game.status.codedGameState]
              .bottom_display === "current"
          ) {
            let away = game.content.media.epg
              .find(e => e.title === "MLBTV")
              .items.find(i => i.mediaFeedType === "AWAY");
            if (away) {
              self.video_feeds[parseInt(away.mediaFeedSubType)] = away;
            }
            let home = game.content.media.epg
              .find(e => e.title === "MLBTV")
              .items.find(i => i.mediaFeedType === "HOME");
            if (home) {
              self.video_feeds[parseInt(home.mediaFeedSubType)] = home;
            }
          }
        }
      });
    },
    toggleSwitchImmediately() {
      let immediate = true;
      if (
        this.priorities
          .slice(0, this.priorities.length - 1)
          .filter(p => !p.immediate).length === 0
      ) {
        immediate = false;
      }
      for (
        var priorityIndex = 0;
        priorityIndex < this.priorities.length - 1;
        priorityIndex++
      ) {
        this.priorities[priorityIndex].immediate = immediate;
      }
    }
  },
  computed: {
    ordered_filtered_games() {
      let leverage_index_thresholds = [
        { display: ">= 10.0", id: 10 },
        { display: ">= 9.0", id: 9 },
        { display: ">= 8.0", id: 8 },
        { display: ">= 7.0", id: 7 },
        { display: ">= 6.0", id: 6 },
        { display: ">= 5.0", id: 5 },
        { display: ">= 4.5", id: 4.5 },
        { display: ">= 4.0", id: 4.0 },
        { display: ">= 3.5", id: 3.5 },
        { display: ">= 3.0", id: 3.0 },
        { display: ">= 2.5", id: 2.5 },
        { display: ">= 2.0", id: 2.0 },
        { display: ">= 1.5", id: 1.5 },
        { display: ">= 1.0", id: 1.0 },
        { display: ">= 0.5", id: 0.5 },
        { display: ">= 0.0", id: 0 }
      ];
      const missed_statuses = this.games.filter(
        g =>
          typeof window.game_status_inds[g.status.codedGameState] ===
          "undefined"
      );
      if (missed_statuses.length > 0) {
        console.log("MISSED STATUS", missed_statuses);
      }
      return this.games
        .filter(
          game =>
            window.game_status_inds[game.status.codedGameState].main_display !==
              "linescore" ||
            game.linescore.inningState === "Top" ||
            game.linescore.inningState === "Bottom"
        )
        .sort(this.sortGames);
    },
    currentPriorityIndex() {
      return this.priorities
        ? this.priorities.findIndex(
            priority =>
              priority.type === this.reason_priority.type &&
              priority.data === this.reason_priority.data
          )
        : null;
    },
    currentPriorityDescription() {
      if (this.current_game && this.reason_priority) {
        switch (this.reason_priority.type) {
          case "bat":
            if (
              this.current_game.linescore.offense.batter &&
              this.reason_priority.data ==
                this.current_game.linescore.offense.batter.id
            ) {
              return (
                this.current_game.linescore.offense.batter.fullName + " at bat"
              );
            } else if (
              this.current_game.linescore.offense.ondeck &&
              this.reason_priority.data ==
                this.current_game.linescore.offense.ondeck.id
            ) {
              return (
                this.current_game.linescore.offense.ondeck.fullName + " on deck"
              );
            }
            return "";
          case "pit":
            return (
              this.current_game.linescore.defense.pitcher.fullName + " pitching"
            );
          case "run":
            if (
              this.current_game.linescore.offense.first &&
              this.current_game.linescore.offense.first ==
                this.reason_priority.data
            ) {
              return (
                this.current_game.linescore.offense.first.fullName + " on first"
              );
            } else if (
              this.current_game.linescore.offense.second &&
              this.current_game.linescore.offense.second ==
                this.reason_priority.data
            ) {
              return (
                this.current_game.linescore.offense.second.fullName +
                " on second"
              );
            } else if (
              this.current_game.linescore.offense.third &&
              this.current_game.linescore.offense.third ==
                this.reason_priority.data
            ) {
              return (
                this.current_game.linescore.offense.third.fullName + " on third"
              );
            } else {
              return "";
            }
          case "team":
            return (
              this.teams.find(t => t.id == this.reason_priority.data).name +
              " playing"
            );
          case "team_bat":
            return (
              (this.current_game.linescore.inningHalf === "Bottom"
                ? this.teams.find(
                    t => t.id == this.current_game.teams.home.team.id
                  ).name
                : this.teams.find(
                    t => t.id == this.current_game.teams.away.team.id
                  ).name) + " batting"
            );
          case "team_pit":
            return (
              (this.current_game.linescore.inningHalf === "Bottom"
                ? this.teams.find(
                    t => t.id == this.current_game.teams.away.team.id
                  ).name
                : this.teams.find(
                    t => t.id == this.current_game.teams.home.team.id
                  ).name) + " batting"
            );
          case "LI":
            return "leverage index >= " + this.reason_priority.data;
          case "NoNo":
            return "No-Hitter through " + this.reason_priority.data;
          case "GameSit":
            let inning = parseInt(this.reason_priority.data.substring(7, 1));
            return (
              (this.reason_priority.data.substring(-3) === "tie"
                ? "Tie game through "
                : "One-run game through ") +
              inning +
              " innings"
            );
          case "Misc":
            switch (this.reason_priority.data) {
              case "PosP_pit":
                return (
                  "Position player pitching (" +
                  this.current_game.linescore.defense.pitcher.fullName +
                  ")"
                );
              case "extra":
                return "Extra-inning game";
              case "replay":
                console.log("REPLAY CHALLENGE: GO FIND THE DESCRIPTION");
                return "Replay challenge";
              case "21Ks":
                return (
                  "Chance at 21 Strikeouts (" +
                  this.current_game.linescore.defense.pitcher.fullName +
                  ")"
                );
              default:
                console.log("Missed Misc case: ", this.reason_priority);
                return "";
            }
          case "":
          case "no-preference-items-were-met":
            return;
          default:
            console.log(
              "Priority Type `" +
                this.reason_priority.type +
                "` not yet implemented"
            );
            return "NOT YET IMPLEMENTED";
        }
      } else {
        return "";
      }
    },
    currentlyShowing() {
      return this.teams && this.current_game
        ? this.teams.find(t => t.id == this.current_game.teams.away.team.id)
            .tbg_abbr +
            " @ " +
            this.teams.find(t => t.id == this.current_game.teams.home.team.id)
              .tbg_abbr
        : "";
    }
  },
  components: { Game, draggable }
};
</script>

<style scoped>
.xdrag-element.inactive {
  opacity: 0.5;
}
.xdrag-element {
  display: inline-block;
  background-color: #eee;
  border: 0.1rem solid #ccc;
  border-radius: 0.2rem;
  cursor: move;
  padding: 0.5rem;
  opacity: 1;
}
</style>

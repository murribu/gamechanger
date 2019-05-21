import { mount } from "@vue/test-utils";
import Game from "./Game";

describe("Game", () => {
  test("is a Vue instance", () => {
    const wrapper = mount(Game);
    expect(wrapper.isVueInstance()).toBeTruthy();
  });
});

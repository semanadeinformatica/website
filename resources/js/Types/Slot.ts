import type Model from "@/Types/Model";
import type Quest from "@/Types/Quest";

export default interface Slot extends Model {
    total_quests: number;

    quests?: Quest[];
}

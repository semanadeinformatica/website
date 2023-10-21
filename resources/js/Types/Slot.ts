import type Model from "@/Types/Model";
import type Quest from "@/Types/Quest";

export default interface Slot extends Model {
    total_quests: number;
    points: number;
    name: string;

    quest_count?: number;
    completed_count?: number;

    image_slot_url?: string;

    quests?: Quest[];
}

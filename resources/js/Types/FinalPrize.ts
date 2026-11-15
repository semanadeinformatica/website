import type Model from "./Model";

export default interface FinalPrize extends Model {
    edition_id: number;
    place: number;
    title: string;
    image_path: string | null;
    final_prize_image_url: string | null;
}

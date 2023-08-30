import type Model from "./Model";
import type SocialMedia from "./SocialMedia";

export default interface Company extends Model {
    tier: "PLATINUM" | "GOLD" | "SILVER";
    user_id: number;
    social_media_id?: number;
    social_media?: SocialMedia;
}

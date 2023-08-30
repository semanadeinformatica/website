import type Model from "./Model";
import type SocialMedia from "./SocialMedia";

export default interface Student extends Model {
    user_id: number;
    social_media_id?: number;
    social_media?: SocialMedia;
}

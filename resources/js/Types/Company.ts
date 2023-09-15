import type Model from "./Model";
import type SocialMedia from "./SocialMedia";
import type { User } from "./User";

export default interface Company extends Model {
    user_id: number;
    user?: User;
    social_media_id?: number;
    social_media?: SocialMedia;
}

import type Model from "./Model";

export default interface SocialMedia extends Model {
    email?: string;
    facebook?: string;
    github?: string;
    instagram?: string;
    linkedin?: string;
    twitter?: string;
    website?: string;
}

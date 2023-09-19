import type Admin from "./Admin";
import type Company from "./Company";
import type Model from "./Model";
import type Participant from "./Participant";

interface BaseUser extends Model {
    current_team_id?: number;
    email_verified_at?: string;
    email: string;
    name: string;
    profile_photo_path?: string;
    profile_photo_url: string;
    two_factor_confirmed_at?: string;
    two_factor_enabled: boolean;
    usertype_id: number;
}

interface ParticipantUser extends BaseUser {
    usertype_type: "App\\Models\\Participant";
    usertype?: Participant;
}

interface CompanyUser extends BaseUser {
    usertype_type: "App\\Models\\Company";
    usertype?: Company;
}

interface AdminUser extends BaseUser {
    usertype_type: "App\\Models\\Admin";
    usertype?: Admin;
}

export type User = ParticipantUser | CompanyUser | AdminUser;

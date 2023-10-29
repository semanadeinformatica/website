import type Competition from "./Competition";
import type { User } from "./User";

export default interface MyPageProps {
    jetstream: {
        canCreateTeams: boolean;
        canManageTwoFactorAuthentication: boolean;
        canUpdatePassword: boolean;
        canUpdateProfileInformation: boolean;
        hasEmailVerification: boolean;
        flash?: {
            banner: string;
            bannerStyle: string;
            bannerId: string;
        };
        hasAccountDeletionFeatures: boolean;
        hasApiFeatures: boolean;
        hasTeamFeatures: boolean;
        hasTermsAndPrivacyPolicyFeature: boolean;
        managesProfilePhotos: boolean;
    };
    auth: {
        user?: User;
    };
    canResetPassword: boolean;
    competitions: Competition[];
}

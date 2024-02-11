using LOSPAÉ.ViewModels;
using Word = Microsoft.Office.Interop.Word;
using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Windows.Storage.Pickers;
using Windows.Storage;

namespace LOSPAÉ.Views;

public sealed partial class SchoolDetentionGeneratorPage : Page
{
    public SchoolDetentionGeneratorViewModel ViewModel
    {
        get;
    }

    public SchoolDetentionGeneratorPage()
    {
        ViewModel = App.GetService<SchoolDetentionGeneratorViewModel>();
        InitializeComponent();
        Loaded += SchoolDetentionGeneratorPage_Loaded;
    }

    private void SchoolDetentionGeneratorPage_Loaded(object sender, RoutedEventArgs e)
    {
        StudentsListUpdate();
        StudentSelector.SelectedIndex = 0;
    }

    public void StudentsListUpdate()
    {
        if (App.etudiants.Count > 0)
        {
            StudentSelector.Items.Clear();

            foreach (Etudiant etudiant in App.etudiants)
            {
                StudentSelector.Items.Add(etudiant.EtudiantName);
            }
        }
    }

    private async void SchoolDetentionNotificationGeneration_Click(object sender, RoutedEventArgs e)
    {
        if (StudentSelector.SelectedIndex >= 0 && SchoolDetentionReason.Text.Length > 0 && SchoolDetentionWorkToDo.Text.Length > 0 && SchoolDetentionDuration.Value >= 1)
        {
            object oEndOfDoc = "\\endofdoc"; /* \endofdoc is a predefined bookmark */

            // Verrouillage de la fenêtre
            StudentSelector.IsEnabled = false;
            SchoolDetentionReason.IsEnabled = false;
            SchoolDetentionWorkToDo.IsEnabled = false;
            SchoolDetentionDuration.IsEnabled = false;
            SchoolDetentionNotificationGeneration.IsEnabled = false;
            SchoolDetentionGenerationProgress.IsActive = true;

            // Avertissement concernant les instances ouvertes de Word
            ContentDialog wordForceExitNeededDialog = new ContentDialog()
            {
                XamlRoot = this.XamlRoot,
                Title = "LOSPAÉ",
                Content = "Pour pouvoir générer un bulletin de retenue, Microsoft Word doit être fermé : il sera utilisé par LOSPAÉ pour créer le document automatiquement." + Environment.NewLine + "Si Word est actuellement ouvert, sauvegardez les documents ouverts et fermez Word avant de continuer." + Environment.NewLine + "Si Word est encore ouvert lorsque vous cliquez sur le bouton ci-dessous, toutes les modifications non sauvegardées seront perdues.",
                CloseButtonText = "J'ai compris",
                DefaultButton = ContentDialogButton.Close
            };

            await wordForceExitNeededDialog.ShowAsync();

            // Détection d'une instance de Word et fermeture le cas échéant
            CloseAllWordInstances();

            // Démarrage Word et création d'un document vierge à partir du modèle
            Word._Application oWord;
            Word._Document oDoc;
            oWord = new Word.Application();
            oWord.Visible = false;
            oDoc = oWord.Documents.Add(Environment.CurrentDirectory + "\\Assets\\MOD_BR.dotx");

            // Mise à jour du document via les champs du modèle
            oDoc.SelectContentControlsByTag("Date")[1].Range.Text = DateTime.Now.ToShortDateString();
            oDoc.SelectContentControlsByTag("EtudiantName")[1].Range.Text = App.etudiants[StudentSelector.SelectedIndex].EtudiantName;
            oDoc.SelectContentControlsByTag("EtudiantClassName")[1].Range.Text = App.etudiants[StudentSelector.SelectedIndex].EtudiantClassName;
            oDoc.SelectContentControlsByTag("Temps")[1].Range.Text = SchoolDetentionDuration.Text;
            oDoc.SelectContentControlsByTag("Motif")[1].Range.Text = SchoolDetentionReason.Text;
            oDoc.SelectContentControlsByTag("Travail")[1].Range.Text = SchoolDetentionWorkToDo.Text;
            oDoc.SelectContentControlsByTag("Professeur")[1].Range.Text = "M. KREICHER";

            // Récupération du chemin et du nom du fichier de destination
            FileSavePicker savePicker = new FileSavePicker();
            var window = App.MainWindow;
            var hWnd = WinRT.Interop.WindowNative.GetWindowHandle(window);
            WinRT.Interop.InitializeWithWindow.Initialize(savePicker, hWnd);
            savePicker.SuggestedStartLocation = PickerLocationId.DocumentsLibrary;
            savePicker.FileTypeChoices.Add("Fichier PDF", new List<string>() { ".pdf" });
            savePicker.SuggestedFileName = "Retenue - " + App.etudiants[StudentSelector.SelectedIndex].EtudiantName + " - 11.02.2024.pdf";
            StorageFile file = await savePicker.PickSaveFileAsync();

            // Enregistrement et fermeture Word
            oDoc.SaveAs2(file.Path, Word.WdSaveFormat.wdFormatPDF);
            oDoc = null;
            oWord.Quit();
            oWord = null;

            // Fermeture forcée de Word pour éviter une fenêtre "Enregistrer sous" inutile
            CloseAllWordInstances();

            // Déverrouillage et réinitialisation partielle de la fenêtre
            StudentSelector.IsEnabled = true;
            StudentSelector.SelectedIndex = 0;
            SchoolDetentionReason.IsEnabled = true;
            SchoolDetentionReason.Text = "";
            SchoolDetentionWorkToDo.IsEnabled = true;
            SchoolDetentionWorkToDo.Text = "";
            SchoolDetentionDuration.IsEnabled = true;
            SchoolDetentionDuration.Value = 1;
            SchoolDetentionNotificationGeneration.IsEnabled = true;
            SchoolDetentionGenerationProgress.IsActive = false;

            ContentDialog documentGeneratedDialog = new ContentDialog()
            {
                XamlRoot = this.XamlRoot,
                Title = "LOSPAÉ",
                Content = "Le bulletin de retenue a été généré et est disponible au chemin ci-dessous." + Environment.NewLine + file.Path,
                CloseButtonText = "OK",
                DefaultButton = ContentDialogButton.Close
            };

            await documentGeneratedDialog.ShowAsync();
        }
        else
        {
            ContentDialog missingDataDialog = new ContentDialog()
            {
                XamlRoot = this.XamlRoot,
                Title = "LOSPAÉ",
                Content = "Certaines informations du bulletin de retenue ne sont pas correctement remplies. Vérifiez les champs et relancez la génération.",
                CloseButtonText = "OK",
                DefaultButton = ContentDialogButton.Close
            };

            await missingDataDialog.ShowAsync();
        }
    }

    private void CloseAllWordInstances()
    {
        foreach (System.Diagnostics.Process item in System.Diagnostics.Process.GetProcesses())
        {
            if (item.ProcessName == "WINWORD")
                item.Kill();
        }
    }
}
